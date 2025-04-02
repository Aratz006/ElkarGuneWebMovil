<?php
class ErreserbaElementua {
    private $idErreserbaElementua;
    private $idErreserba;
    private $idEspazioa;

    public function erreEleIkusi($idBazkidea, $mota, $erreserbaData) {
        global $pdo;
        $select = "SELECT e.izena 
                   FROM espazioa e 
                   JOIN erreserbaelementua ee ON e.idEspazioa = ee.idEspazioa 
                   JOIN erreserba er ON ee.idErreserba = er.idErreserba 
                   WHERE er.idBazkidea = :idBazk 
                   AND er.mota = :mota 
                   AND er.data = :data";

        $cmd = $pdo->prepare($select);
        $cmd->bindValue(':idBazk', $idBazkidea);
        $cmd->bindValue(':mota', $mota);
        $cmd->bindValue(':data', date('Y-m-d', strtotime($erreserbaData)));
        $cmd->execute();
        return $cmd->fetchAll(PDO::FETCH_ASSOC);
    }

    public function erreEleGehitu($idErreserba, $idEspazioa) {
        global $pdo;
        $insertQuery = "INSERT INTO erreserbaelementua (idErreserba, idEspazioa) 
                        VALUES (:idErreserba, :idEspazioa)";
        $insertCmd = $pdo->prepare($insertQuery);
        $insertCmd->bindValue(':idErreserba', $idErreserba);
        $insertCmd->bindValue(':idEspazioa', $idEspazioa);
        return $insertCmd->execute();
    }

    public function erreEleEzabatu($idErreserba) {
        global $pdo;
        $deleteQuery = "DELETE FROM erreserbaelementua WHERE idErreserba = :idErreserba";
        $deleteCmd = $pdo->prepare($deleteQuery);
        $deleteCmd->bindValue(':idErreserba', $idErreserba);
        return $deleteCmd->execute();
    }

    public function erreEleEgiaztatu($idEspazioa, $data, $mota) {
        global $pdo;
        $select = "SELECT COUNT(*) 
                   FROM erreserbaelementua ee 
                   JOIN erreserba er ON ee.idErreserba = er.idErreserba 
                   WHERE ee.idEspazioa = :idEspazioa 
                   AND er.data = :data 
                   AND er.mota = :mota";

        $cmd = $pdo->prepare($select);
        $cmd->bindValue(':idEspazioa', $idEspazioa);
        $cmd->bindValue(':data', date('Y-m-d', strtotime($data)));
        $cmd->bindValue(':mota', $mota);
        $cmd->execute();
        return $cmd->fetchColumn() > 0;
    }
}