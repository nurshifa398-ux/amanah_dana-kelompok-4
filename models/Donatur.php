<?php
require_once __DIR__ . '/../config/Database.php';
class Donatur {
 private PDO $db;
 public function __construct(){ $this->db=Database::connect(); }
 public function getAll():array{return $this->db->query('SELECT * FROM donatur ORDER BY nama')->fetchAll();}
 public function getById(int $id){$s=$this->db->prepare('SELECT * FROM donatur WHERE id=?');$s->execute([$id]);return $s->fetch();}
 public function create(array $d):int{$last=(int)$this->db->query("SELECT COALESCE(MAX(CAST(SUBSTRING(kode,5) AS UNSIGNED)),0) FROM donatur WHERE kode REGEXP '^DNT-[0-9]+$'")->fetchColumn();$kode='DNT-'.str_pad((string)($last+1),3,'0',STR_PAD_LEFT);$s=$this->db->prepare('INSERT INTO donatur(kode,nama,jenis,telepon,alamat) VALUES(?,?,?,?,?)');$s->execute([$kode,$d['nama'],$d['jenis'],$d['telepon']?:null,$d['alamat']?:null]);return (int)$this->db->lastInsertId();}
 public function update(int $id,array $d):void{$s=$this->db->prepare('UPDATE donatur SET nama=?,jenis=?,telepon=?,alamat=? WHERE id=?');$s->execute([$d['nama'],$d['jenis'],$d['telepon']?:null,$d['alamat']?:null,$id]);if(!$s->rowCount()&&!$this->getById($id))throw new RuntimeException('Donatur tidak ditemukan.');}
 public function delete(int $id):void{try{$s=$this->db->prepare('DELETE FROM donatur WHERE id=?');$s->execute([$id]);if(!$s->rowCount())throw new RuntimeException('Donatur tidak ditemukan.');}catch(PDOException $e){if($e->getCode()==='23000')throw new RuntimeException('Donatur tidak bisa dihapus karena sudah digunakan pada transaksi penerimaan.');throw $e;}}
}
