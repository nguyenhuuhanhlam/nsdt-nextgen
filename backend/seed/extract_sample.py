"""Trích xuất dữ liệu mẫu từ database hệ cũ (MySQL 5.1) -> seed/sample_data.json.

CHỈ ĐỌC (SELECT), không ghi gì lên server cũ.

Cấu hình qua biến môi trường (không hardcode thông tin server trong repo):
    NSDT_OLD_HOST, NSDT_OLD_PORT, NSDT_OLD_USER, NSDT_OLD_PASS, NSDT_OLD_DB

Ẩn danh hóa trước khi ghi file:
- họ tên -> tên Việt giả theo giới tính (seed cố định 20261015 để tái tạo được)
- KHÔNG trích xuất số CMND, địa chỉ
- ngày zero kiểu '1985-00-00' (chỉ biết năm) -> null
Các trường nghiệp vụ (mã NV, mã thẻ, đơn vị, chức vụ, công việc, ngày tháng,
hệ số lương, ngày nghỉ việc) giữ nguyên để dữ liệu mẫu đúng thực tế.
"""
import json, os, random, re, pymysql
from datetime import date

conn = pymysql.connect(host=os.environ['NSDT_OLD_HOST'], port=int(os.environ['NSDT_OLD_PORT']),
    user=os.environ['NSDT_OLD_USER'], password=os.environ['NSDT_OLD_PASS'],
    database=os.environ['NSDT_OLD_DB'], charset='utf8',
    connect_timeout=90, read_timeout=300)
cur = conn.cursor()

def rows(sql, args=None):
    cur.execute(sql, args or ())
    cols = [d[0] for d in cur.description]
    return [dict(zip(cols, r)) for r in cur.fetchall()]

_ZERO = re.compile(r'^\d{4}-00|-\d{2}-00$|0000')
def iso(v):
    # DB cũ có ngày zero kiểu '1985-00-00' (chỉ biết năm) -> None
    if v is None: return None
    s = v.isoformat() if isinstance(v, date) else str(v)
    if _ZERO.search(s): return None
    return s

catalogs = {
    'departments': rows("SELECT id_donvi AS old_id, donvi AS name FROM ns_donvi ORDER BY id_donvi"),
    'positions': rows("SELECT id_chucvu AS old_id, chucvu AS name FROM ns_chucvu ORDER BY id_chucvu"),
    'job_titles': rows("SELECT id_congviec AS old_id, congviec AS name FROM ns_congviec ORDER BY id_congviec"),
    'provinces': rows("SELECT id_tinhthanh AS old_id, tinhthanh AS name FROM ns_tinhthanh ORDER BY id_tinhthanh"),
    'maternity_types': rows("SELECT id_thaisan AS old_id, thaisan AS name FROM ns_thaisan ORDER BY id_thaisan"),
}

emps = rows("""(SELECT ma_nhanvien, ma_the, gioitinh, ngaysinh, id_donvi, id_chucvu, id_congviec,
    ngayvaolam, ngayhetthuviec, hesoluong, ngay_nghiviec
    FROM ns_nhansu WHERE ngay_nghiviec IS NULL ORDER BY RAND() LIMIT 240)
    UNION ALL
    (SELECT ma_nhanvien, ma_the, gioitinh, ngaysinh, id_donvi, id_chucvu, id_congviec,
    ngayvaolam, ngayhetthuviec, hesoluong, ngay_nghiviec
    FROM ns_nhansu WHERE ngay_nghiviec IS NOT NULL ORDER BY RAND() LIMIT 60)""")
print("sampled employees:", len(emps))

codes = [e['ma_nhanvien'] for e in emps]
ph = ",".join(["%s"] * len(codes))
mat_leaves = rows(
    f"""SELECT ma_nhanvien AS employee_code, id_thaisan AS maternity_type_old_id,
    ngay_batdau AS start_date, ngay_ketthuc AS end_date, songaynghi AS leave_days
    FROM ns_xuly_thaisan WHERE ma_nhanvien IN ({ph})""", codes)
print("maternity leaves:", len(mat_leaves))
conn.close()

HO = ['Nguyễn','Trần','Lê','Phạm','Hoàng','Huỳnh','Phan','Vũ','Võ','Đặng','Bùi','Đỗ','Hồ','Ngô','Dương','Lý','Trương','Đinh']
DEM_NU = ['Thị','Ngọc','Thu','Thanh','Phương','Kim','Mỹ','Bích','Hồng','Lan','Diệu','Quỳnh']
DEM_NAM = ['Văn','Hữu','Đức','Minh','Quốc','Xuân','Hoàng','Thế','Đình','Công','Bá','Trọng']
TEN_NU = ['An','Bình','Chi','Dung','Hà','Hương','Lan','Linh','Mai','Ngọc','Nhung','Phúc','Thảo','Thu','Trang','Yến','Nhi','Trâm']
TEN_NAM = ['An','Bình','Dũng','Hùng','Long','Minh','Nam','Phúc','Quân','Sơn','Thành','Tuấn','Tú','Vinh','Cường','Hải','Khoa','Lâm']
rnd = random.Random(20261015)

def fake_name(gender):
    ho = rnd.choice(HO)
    if gender == 'Nữ':
        return f"{ho} {rnd.choice(DEM_NU)}", rnd.choice(TEN_NU)
    return f"{ho} {rnd.choice(DEM_NAM)}", rnd.choice(TEN_NAM)

out_emps = []
for e in emps:
    ho, ten = fake_name(e['gioitinh'])
    out_emps.append({
        "employee_code": e['ma_nhanvien'],
        "card_id": e['ma_the'],
        "first_name": ho,
        "last_name": ten,
        "gender": e['gioitinh'],
        "birth_date": iso(e['ngaysinh']),
        "department_old_id": e['id_donvi'],
        "position_old_id": e['id_chucvu'],
        "job_title_old_id": e['id_congviec'],
        "join_date": iso(e['ngayvaolam']),
        "probation_end_date": iso(e['ngayhetthuviec']),
        "salary_coefficient": float(e['hesoluong']) if e['hesoluong'] is not None else None,
        "is_active": e['ngay_nghiviec'] is None,
        "terminated_date": iso(e['ngay_nghiviec']),
    })

data = {**catalogs,
    "employees": out_emps,
    "maternity_leaves": [
        {**m, "start_date": iso(m['start_date']), "end_date": iso(m['end_date']), "notes": ""}
        for m in mat_leaves
    ],
    "_meta": {
        "note": "Dữ liệu mẫu đã ẨN DANH HÓA: họ tên là dữ liệu giả theo phân bố thực tế (seed 20261015); không gồm CMND/địa chỉ; ngày zero kiểu '1985-00-00' -> null.",
        "source": "ns_nhansu (mẫu ngẫu nhiên 300/1027: 240 đang làm + 60 đã nghỉ)",
    },
}
p = "/home/hatch/workspace/nsdt-nextgen/backend/seed/sample_data.json"
os.makedirs(os.path.dirname(p), exist_ok=True)
with open(p, "w", encoding="utf-8") as f:
    json.dump(data, f, ensure_ascii=False, indent=1)
print("wrote", p, len(json.dumps(data)) // 1024, "KB")
