<?php

define("BD_HOST","localhost");
define("BD_USER","root");
define("BD_PASS","");
define("BD_NAME","pruebas");

class BaseDatos {
	private $bd;
	public function __construct(){
		try {
		 	$this->bd = new PDO("mysql:dbname=".BD_NAME.";host=".BD_HOST,BD_USER,BD_PASS);
		 } catch (Exception $e) {
		 	?>
		 	<script type="text/javascript">
		 		alert("No se puede conectar a la base de datos");
		 	</script>
		 	<?php
		 	var_dump($e);
		 }
	}

	public function insert($tabla, $campos, $valores) {
		$campos2 = array();
		$comodines = array();

		$consulta = "INSERT INTO $tabla(";
		foreach ($campos as $c) {
			$campos2[] = $c;
		}
		$strcampos = implode(",", $campos2);

		$consulta .= $strcampos.") VALUES (";
		foreach ($valores as $v ) {
			$comodines[] = "?";
		}
		$strcomodines = implode(",", $comodines);
		$consulta .= $strcomodines . ")";
		$sql = $this->bd->prepare($consulta);
		if($sql->execute($valores))
			return 1;
		else
			return $sql->errorInfo();
	}

	public function selectGeneral($tabla){
		$sql = $this->bd->prepare("SELECT * FROM $tabla");
		$sql->execute();
		$results = $sql->fetchAll(PDO::FETCH_ASSOC);
		return $results;
	}

	public function ejecutar($sqlR){
		$sql = $this->bd->prepare($sqlR);
	    if($sql->execute()){
	        $results = $sql->fetchAll(PDO::FETCH_ASSOC);
	        $conteo=count($results);
	        if($conteo==0)
	          return 0;
	        else
	          return $results;
	    }
	    else
	      return var_dump($sql);
  	}

	public function bulk_insert($tabla,$campos,$strows) {
		$sql = $this->bd->prepare("INSERT INTO $tabla($campos) VALUES $strows");
		$res = $sql->execute();
		if($res)
			return 1;
		else
			return $sql->errorInfo();
	}

	public function login($usuario,$clave) {
		$sql = $this->bd->prepare("SELECT * FROM docente WHERE carnet=:carnet AND clave=:clave AND estado='Activo' AND accesosistemas='1' AND esadministrador='1'");
		$sql->bindParam(':carnet',$usuario,PDO::PARAM_STR);
		$sql->bindParam(':clave',$clave,PDO::PARAM_STR);

		if($sql->execute()){
			$filas = $sql->fetchAll(PDO::FETCH_ASSOC);
			if(count($filas)>0) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	public function mensajeBienvenida($carnet) {
		$sql = $this->bd->prepare("SELECT CONCAT(nom_usuario,' ',ape_usuario) AS nombre FROM docente WHERE carnet=:carnet");
		$sql->bindParam(':carnet',$carnet,PDO::PARAM_STR);

		if($sql->execute()){
			$filas = $sql->fetchAll(PDO::FETCH_ASSOC);
			if(count($filas)>0) {
				return $filas[0]['nombre'];
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	public function controlarCorriente($sentencia,$aula,$usuario) {
		$fecha = Date('Y-m-d');
		$hora = Date("H:i:s");
		$sql = $this->bd->prepare("INSERT INTO `baseiot2018`.`encender`(`tarjeta`,`dispositivo`,`dato`,`fecha`,`hora`)VALUES ('T,1','Luces',:sentencia,'$fecha','$hora')");
		$sql->bindParam(':sentencia',$sentencia);
		if($sql->execute()){
			$id = $this->bd->lastInsertId();
			$sql2 = $this->bd->prepare("UPDATE encenderhistorial SET usuario=:usuario, aula=:aula WHERE iddato=:id");
			$sql2->bindParam(':usuario',$usuario);
			$sql2->bindParam(':aula',$aula);
			$sql2->bindParam(':id',$id);
			if($sql2->execute()){
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
}
?>
