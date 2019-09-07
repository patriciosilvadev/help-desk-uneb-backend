<?php
require_once("dao.php");
require_once("chamado.php");
require_once("chamado_ti.php");
require_once("alteracao_dao.php");
require_once("tecnico.php");
require_once("usuario.php");
require_once("setor.php");

class ChamadoDAO extends DAO {
   private $table = "tchamado";

   protected function read($query, $populate = array()) {
      $resultadoDB = $this->conn->prepare($query);
      $resultadoDB->execute();
      $chamados = array();
      if($resultadoDB->rowCount() > 0) {
         while(($row = $resultadoDB->fetch(PDO::FETCH_ASSOC))) {
            $novoChamado = new Chamado($row["id"]);
            $novoChamado->setDescricao($row["descricao"]);
            $novoChamado->setData($row["data"]);
            $novoChamado->setTombo($row["tombo"]);
            $alteracaoDAO = new AlteracaoDAO();
            $alteracaoDAO->readByChamado($novoChamado);
            $novoChamado->setAlteracoes($alteracaoDAO->readByChamado($novoChamado));
            $setor = new Setor();
            $setor->setID($row["id_setor"]);
            $novoChamado->setSetor($setor->read());
            if($populate["tecnico"]) {
               $tecnico = new Tecnico();
               $tecnico->setLogin($row["id_tecnico"]);
               // var_dump($row);
               // var_dump($tecnico);
               $novoChamado->setTecnico($tecnico->read(array("chamados" => false)));
            }
            if($populate["usuario"]) {
               $usuario = new Usuario();
               $usuario->setCPF($row["id_usuario"]);
               $novoChamado->setUsuario($usuario->read(array("chamados" => false)));
            }
            array_push($chamados, $novoChamado);
         }
      }
      return $chamados;
   }

   public function readByUsuario($usuario) {
      $query = "SELECT * FROM $this->table WHERE id_usuario = " . $usuario->getCPF();
      return $this->read($query, array("tecnico" => true, "usuario" => false));
      // QUERY INCOMPLETA
   }

   public function readByTecnico($tecnico) {
      $query = "SELECT * FROM $this->table WHERE id_tecnico = '" . $tecnico->getLogin() . "'";
      return $this->read($query, array("tecnico" => false, "usuario" => true));
   }

   public function readByID($chamado, $populate = array()) {
      $resultadoDB = $this->conn->prepare("SELECT * FROM $this->table WHERE id = :id");
      $resultadoDB->bindValue(":id", $chamado->getID(), PDO::PARAM_INT);
      $resultadoDB->execute();
      if($resultadoDB->rowCount()) {
        $row = $resultadoDB->fetch(PDO::FETCH_ASSOC);
        $chamado->setDescricao($row["descricao"]);
        $chamado->setData($row["data"]);
        $chamado->setTombo($row["tombo"]);
        $alteracaoDAO = new AlteracaoDAO();
        $alteracaoDAO->readByChamado($chamado);
        $chamado->setAlteracoes($alteracaoDAO->readByChamado($chamado));
      //   var_dump($chamado->getAlteracoes()[0]->getJSON());
      //   var_dump($chamado->getAlteracoes())
        $setor = new Setor();
        $setor->setID($row["id_setor"]);
        $chamado->setSetor($setor->read());
        if($populate["tecnico"]) {
           $tecnico = new Tecnico();
           $tecnico->setLogin($row["id_tecnico"]);
           $chamado->setTecnico($tecnico->read(array("chamados" => false)));
        }
        if($populate["usuario"]) {
           $usuario = new Usuario();
           $usuario->setCPF($row["id_usuario"]);
           $chamado->setUsuario($usuario->read(array("chamados" => false)));
        }
        return $chamado;
      }
      return false;
   }

   public function readEmAberto() {
      $query = "SELECT chamado.id, chamado.descricao, chamado.data, chamado.ti, 
                        chamado.tombo, chamado.id_tecnico, chamado.id_usuario,
                        chamado.id_setor
               FROM tchamado chamado
               LEFT JOIN talteracao alteracao
                  ON (chamado.id = alteracao.id_chamado)
               WHERE alteracao.id_chamado IS NULL";
      $resultadoDB = $this->conn->prepare($query);
      $resultadoDB->execute();
      $chamados = array();
      while($row = $resultadoDB->fetch(PDO::FETCH_ASSOC)) {
         $chamado = new Chamado();
         $chamado->setID($row["id"]);
         $chamado->setDescricao($row["descricao"]);
         $chamado->setData($row["data"]);
         $chamado->setTombo($row["tombo"]);
         $setor = new Setor();
         $setor->setID($row["id_setor"]);
         $chamado->setSetor($setor->read());
         $tecnico = new Tecnico();
         $tecnico->setLogin($row["id_tecnico"]);
         $chamado->setTecnico($tecnico->read(array("chamados" => false)));
         $usuario = new Usuario();
         $usuario->setCPF($row["id_usuario"]);
         $chamado->setUsuario($usuario->read(array("chamados" => false)));
         array_push($chamados, $chamado);
      }
      // var_dump($chamados);
      return $chamados;
   }

   public function delete($chamado) {
      $resultadoDB = $this->conn->prepare("DELETE FROM $this->table WHERE id = :chamado");
      $resultadoDB->bindValue(":chamado", $chamado->getID(), PDO::PARAM_INT);
      // $resultadoDB->debugDumpParams();
      $resultadoDB->execute();
      return $resultadoDB->rowCount();
   }

   public function create($chamado) {

   }
}

?>
