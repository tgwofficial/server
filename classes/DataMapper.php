<?php

defined('BASE_URL') OR exit('No direct script access allowed');

class DataMapper extends Mapper
{
    public function save($form_name, array $data) {
        $keys = array_keys($data);

        $sql = "SHOW columns FROM data_".$form_name;
        try {
            $this->db->query($sql);
        } catch (PDOException $e) {
            $sql = "CREATE TABLE data_".$form_name." (id INT(11) AUTO_INCREMENT PRIMARY KEY, ".implode(' TEXT NOT NULL COLLATE utf8_general_ci, ', $keys)." TEXT NOT NULL COLLATE utf8_general_ci)";
            $this->db->query($sql);
        }

        $sql = "SHOW columns FROM data_".$form_name;
        $stmt = $this->db->query($sql);
        $columns = [];
        while($row = $stmt->fetch()) {
            $columns[$row['Field']] = true;
        }

        $miss_key = [];
        foreach ($keys as $key) {
            if (!array_key_exists($key, $columns)) {
                $miss_key[] = $key;
            }
        }

        if (!empty($miss_key)) {
            $sql = "ALTER TABLE data_".$form_name;
            $adds = [];
            foreach ($miss_key as $key) {
                $adds[] = "ADD COLUMN ".$key." TEXT NOT NULL COLLATE utf8_general_ci";
            }
            $sql = $sql." ".implode(', ', $adds);
            $this->db->query($sql);
        }

        $sql = "INSERT INTO data_".$form_name."(".implode(',', $keys).") values (:".implode(',:', $keys).")";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($data);
        if(!$result) {
            throw new Exception("could not save record");
        }
    }

    public function getReports($loc,$thn,$bln){
        if ($bln=='99') {
            $start_time = $thn."-01-01";
            $end_time = date("Y-m-d", strtotime($start_time." +1 year"));
        }else{
            if ($bln[0]!='0'&&strlen($bln)==1) {
                $bln = '0'.$bln;
            }
            $start_time = $thn."-".$bln."-01";
            $end_time = date("Y-m-d", strtotime($start_time." +1 month"));
        }
        $loc_name = $loc['name'];
        $report = ["ibu_hamil_baru"=>0,"ibu_hamil_aktif"=>0,"ibu_bersalin"=>0,"ibu_meninggal"=>0,"bayi_lahir"=>0,"bayi_meninggal"=>0];

        $sql = "SELECT COUNT(*) as jml
            from data_identitas_ibu
            where data_identitas_ibu.location_id = :location_id
            and data_identitas_ibu.timestamp >= :start_time and data_identitas_ibu.timestamp < :end_time";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(["location_id" => $loc_name,"start_time" => $start_time,"end_time" => $end_time]);
        $row = $stmt->fetch();
        $report['ibu_hamil_baru'] = $row['jml'];

        $sql = "SELECT COUNT(*) as jml
            from data_identitas_ibu
            left join data_status_persalinan
            on data_status_persalinan.id_ibu = data_identitas_ibu.unique_id
            where data_identitas_ibu.location_id = :location_id
            and (data_status_persalinan.tgl_persalinan='' or data_status_persalinan.tgl_persalinan >= :end_time)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(["location_id" => $loc_name,"end_time" => $end_time]);
        $row = $stmt->fetch();
        $report['ibu_hamil_aktif'] = $row['jml'];

        $sql = "SELECT data_identitas_ibu.*,data_status_persalinan.status_bersalin,data_status_persalinan.tgl_persalinan,data_status_persalinan.kondisi_ibu,data_status_persalinan.kondisi_anak
            from data_identitas_ibu
            left join data_status_persalinan
            on data_status_persalinan.id_ibu = data_identitas_ibu.unique_id
            where data_identitas_ibu.location_id = :location_id and tgl_persalinan >= :start_time and tgl_persalinan < :end_time";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(["location_id" => $loc_name,"start_time" => $start_time,"end_time" => $end_time]);
        $results = [];
        while($row = $stmt->fetch()) {
            $results[] = $row;
        }
        foreach ($results as $ibu) {
            if ($ibu['kondisi_ibu']=='hidup') {
                $report['ibu_bersalin']++;
            }
            else{
                $report['ibu_meninggal']++;
            }
            if ($ibu['kondisi_anak']=='hidup') {
                $report['bayi_lahir']++;
            }
            else{
                $report['bayi_meninggal']++;
            }
        }

        return $report;
    }

    public function getYearlyReports($loc,$thn,$bln){
        $reports = [];
        $start_time = $thn."-01-01";
        $end_time = date("Y-m-d", strtotime($start_time." +1 month"));
        for ($i=0; $i < 12; $i++) { 
            $loc_name = $loc['name'];
            $report = ["ibu_hamil_baru"=>0,"ibu_hamil_aktif"=>0,"ibu_bersalin"=>0,"ibu_meninggal"=>0,"bayi_lahir"=>0,"bayi_meninggal"=>0];

            $sql = "SELECT COUNT(*) as jml
                from data_identitas_ibu
                where data_identitas_ibu.location_id = :location_id
                and data_identitas_ibu.timestamp >= :start_time and data_identitas_ibu.timestamp < :end_time";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(["location_id" => $loc_name,"start_time" => $start_time,"end_time" => $end_time]);
            $row = $stmt->fetch();
            $report['ibu_hamil_baru'] = $row['jml'];

            $sql = "SELECT COUNT(*) as jml
                from data_identitas_ibu
                left join data_status_persalinan
                on data_status_persalinan.id_ibu = data_identitas_ibu.unique_id
                where data_identitas_ibu.location_id = :location_id
                and (data_status_persalinan.tgl_persalinan='' or data_status_persalinan.tgl_persalinan >= :end_time)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(["location_id" => $loc_name,"end_time" => $end_time]);
            $row = $stmt->fetch();
            $report['ibu_hamil_aktif'] = $row['jml'];

            $sql = "SELECT data_identitas_ibu.*,data_status_persalinan.status_bersalin,data_status_persalinan.tgl_persalinan,data_status_persalinan.kondisi_ibu,data_status_persalinan.kondisi_anak
                from data_identitas_ibu
                left join data_status_persalinan
                on data_status_persalinan.id_ibu = data_identitas_ibu.unique_id
                where data_identitas_ibu.location_id = :location_id and tgl_persalinan >= :start_time and tgl_persalinan < :end_time";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(["location_id" => $loc_name,"start_time" => $start_time,"end_time" => $end_time]);
            $results = [];
            while($row = $stmt->fetch()) {
                $results[] = $row;
            }
            foreach ($results as $ibu) {
                if ($ibu['kondisi_ibu']=='hidup') {
                    $report['ibu_bersalin']++;
                }
                else{
                    $report['ibu_meninggal']++;
                }
                if ($ibu['kondisi_anak']=='hidup') {
                    $report['bayi_lahir']++;
                }
                else{
                    $report['bayi_meninggal']++;
                }
            }

            $reports[$i] = $report;
            $start_time = date("Y-m-d", strtotime($start_time." +1 month"));
            $end_time = date("Y-m-d", strtotime($start_time." +1 month"));
        }
        

        return $reports;
    }
}
