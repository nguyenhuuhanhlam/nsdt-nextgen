<?
	class _Time{
	
		private $H;//tong gio {0->n}
		private $M;//tong phut {0->n}
		private $h;//gio {0->24}
		private $m;//phut {0->59}
		
		private $hours;
		private $minutes;
		
		private function hmPart($t){
			if($t) return explode(':',$t);
			return 0;
		}
		
		private function hmM($t){
			$t = $this->hmPart($t);
			return (int)$t[0]*60+(int)$t[1];
		}
		
		/*[-]*/
		public function getHours($hours){
			$hours = $this->hmPart($hours);
			return (int)$hours[0];
		}
		/*[-]*/
		public function getMinutes($minutes){
			$minutes = $this->hmPart($minutes);
			return (int)$minutes[0]*60+(int)$minutes[1];
		}
	
		public function hmAdd($t1,$t2){
			$this->M = $this->hmM($t1)+$this->hmM($t2);
			return $this->hmResult();
		}
		
		public function hmSum($tA){
			$this->M = 0;
			foreach($tA as $t) $this->M += $this->hmM($t);
			return $this->hmResult();
		}
		
		
		public function hmSub($t1,$t2){
			$this->M = $this->hmM($t1)-$this->hmM($t2);
			return $this->hmResult();
		}
		
		/* < */
		public function hmLt($t1,$t2){
			return $this->hmM($t1)<$this->hmM($t2) ? true : false;
		}
		
		/* <= */
		public function hmLe($t1,$t2){
			return $this->hmM($t1)<=$this->hmM($t2) ? true : false;
		}
		
		/* > */
		public function hmGt($t1,$t2){
			return $this->hmM($t1)>$this->hmM($t2) ? true : false;
		}
		
		/* >= */
		public function hmGe($t1,$t2){
			return $this->hmM($t1)>=$this->hmM($t2) ? true : false;
		}
		
		/* = */
		public function hmEq($t1,$t2){
			return $this->hmM($t1)==$this->hmM($t2) ? true : false;
		}
		
		public function hmResult(){
			$this->H = (int)($this->M/60);
			$this->m = ($this->M) - ($this->H*60);
			$this->m = ($this->m<=9) ? '0'.$this->m : $this->m;
			return $this->H.':'.$this->m; 
		}
		
		public function hmShow($t,$o=false){
			if($t){
				$t = $this->hmPart($t);
				$o = $o ? '.':':';
				return $t[0].$o.$t[1];
			}else return '-';
		}
		
		public function hmNullShow($t){
			if($t){
				$t = $this->hmPart($t);
				if((int)($t[0])||(int)($t[1])) return  $t[0].':'.$t[1]; else return '-';
			}else return '-';
		}
		
		/*@ham danh rieng*/
		public function officeTime(&$v,$k,$p){
			$v = $this->hmGt($v,$p) ? $p : $this->hmShow($v);
		}
		public function partTime(&$v,$k,$p){
			$this->hmSub($v,$p);
			$v = $this->hmGt($v,$p) ? $this->hmResult() : '0';
		}
		
		public function partTimeNull(&$v){
			$v = '0';
		}
	}
?>