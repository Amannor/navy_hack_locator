<?php

class Coordinate{

	public function __construct($x, $y){
		$this->setX($x);
		$this->setY($y);
		return;
	}

	public function __destruct(){
		return;
	}

	public function Coordinate($x, $y){ // php4 compatibility
		if(version_compare(PHP_VERSION,"5.0.0","<")){
			register_shutdown_function(array($this,"__destruct"));    
			$this->__construct($x, $y);     
		}
		return;
	}
	

	public function getX() {
		return (float) $this->x;
	}

	public function getY() {
		return (float) $this->y;
	}

	public function setX($x) {
		$this->x = $x;
		return;
	}

	public function setY($y) {
		$this->y = $y;
		return;
	}

	public function getDist(Coordinate $co) {
		$xd = $co->getX() - $this->getX();
		$yd = $co->getY() - $this->getY();

		return sqrt(($xd * $xd) + ($yd * $yd));  // sqrt(x^2 + y^2) -- simple enough
	}
	
    // property declaration
	private $x = 0.0;
	private $y = 0.0;
}


class dfcCoordinate extends Coordinate{  // dfc = Distance from Center

	public function __construct($x, $y, $dfc){
		$this->setDFC($dfc);
		parent::__construct($x, $y);
		return;
	}

	public function __destruct(){
		parent::__destruct();
	}

	public function dfcCoordinate($x, $y, $dfc){ // php4 compatibility
		if(version_compare(PHP_VERSION,"5.0.0","<")){
			register_shutdown_function(array($this,"__destruct"));	
			$this->__construct($x, $y, $dfc);     
		}
		return;
	}

	public function getDFC() {
		return (float) $this->dfc;
	}

	public function setDFC($dfc) {
		$this->dfc = $dfc;
		return;
	}

	private $dfc;
}

?>
