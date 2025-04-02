:<?php
class Erreserba {
    private $idErreserba;
    private $idBazkidea;
    private $mota;
    private $data;

    public function erreserbaSartu($idBazkidea, $mota, $erreserbaData) {
        global $pdo;
        $insertQuery = "INSERT INTO erreserba (idBazkidea, mota, data) VALUES (:idBazk, :mota, :data)";
        $insertCmd = $pdo->prepare($insertQuery);
        $insertCmd->bindValue(':idBazk', $idBazkidea);
        $insertCmd->bindValue(':mota', $mota);
        $insertCmd->bindValue(':data', date('Y-m-d', strtotime($erreserbaData)));

        $insertCmd->execute();
        return $pdo->lastInsertId();
    }

    public function erreserbaEguneratu($idErreserba) {
        global $pdo;
        $updateQuery = "UPDATE erreserba e SET komentsalak = (SELECT SUM(esp.gaitasuna) FROM erreserbaelementua ee JOIN espazioa esp ON ee.idEspazioa = esp.idEspazioa WHERE ee.idErreserba = e.idErreserba) WHERE e.idErreserba = :idErreserba";
        $updateCmd = $pdo->prepare($updateQuery);
        $updateCmd->bindValue(':idErreserba', $idErreserba);
        $updateCmd->execute();
    }

    public function erreserbaEzabatu($idErreserba) {
        global $pdo;
        $pdo->beginTransaction();
        try {
            $deleteQuery = "DELETE ee FROM erreserbaelementua ee JOIN erreserba er ON ee.idErreserba = er.idErreserba WHERE er.idErreserba = :idErreserba";
            $deleteCmd = $pdo->prepare($deleteQuery);
            $deleteCmd->bindValue(':idErreserba', $idErreserba);
            $deleteCmd->execute();

            $deleteQuery1 = "DELETE FROM erreserba WHERE idErreserba = :idErreserba";
            $deleteCmd1 = $pdo->prepare($deleteQuery1);
            $deleteCmd1->bindValue(':idErreserba', $idErreserba);
            $deleteCmd1->execute();

            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public function erreserbaIkusi($data) {
        global $pdo;
        $select = "SELECT b.idBazkidea AS 'Bazkidea', e.idErreserba, e.data AS 'Data', 
                   CASE e.mota WHEN 1 THEN 'Bazkaria' WHEN 0 THEN 'Afaria' END AS 'Mota', 
                   e.komentsalak AS 'Komentsalak', 
                   GROUP_CONCAT(esp.izena) AS 'Espazioak'
                   FROM erreserba e 
                   JOIN bazkidea b ON e.idBazkidea = b.idBazkidea 
                   JOIN erreserbaelementua ee ON e.idErreserba = ee.idErreserba
                   JOIN espazioa esp ON ee.idEspazioa = esp.idEspazioa
                   WHERE e.data >= :data 
                   GROUP BY e.idErreserba
                   ORDER BY e.data ASC";
        $cmd = $pdo->prepare($select);
        $cmd->bindValue(':data', date('Y-m-d', strtotime($data)));
        $cmd->execute();
        return $cmd->fetchAll(PDO::FETCH_ASSOC);
    }

    public function historikoaIkusi($idBazkidea, $data) {
        global $pdo;
        $select = "SELECT CONCAT(b.izena, ' ', b.abizenak) AS 'Bazkidea', 
                   e.idErreserba AS 'Erreserba zenbakia', 
                   e.data AS 'Data', 
                   CASE e.mota WHEN 1 THEN 'Bazkaria' WHEN 0 THEN 'Afaria' END AS 'Mota',
                   GROUP_CONCAT(esp.izena) AS 'Espazioak'
                   FROM erreserba e 
                   JOIN bazkidea b ON e.idBazkidea = b.idBazkidea 
                   JOIN erreserbaelementua ee ON e.idErreserba = ee.idErreserba
                   JOIN espazioa esp ON ee.idEspazioa = esp.idEspazioa
                   WHERE e.idBazkidea = :idBazk AND e.data < :data 
                   GROUP BY e.idErreserba
                   ORDER BY e.data DESC";
        $cmd = $pdo->prepare($select);
        $cmd->bindValue(':idBazk', $idBazkidea);
        $cmd->bindValue(':data', date('Y-m-d', strtotime($data)));
        $cmd->execute();
        return $cmd->fetchAll(PDO::FETCH_ASSOC);
    }
}