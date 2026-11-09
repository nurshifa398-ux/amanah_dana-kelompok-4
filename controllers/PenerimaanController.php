<?php
require_once __DIR__.'/Controller.php'; require_once __DIR__.'/../models/Penerimaan.php'; require_once __DIR__.'/../models/Donatur.php';
class PenerimaanController extends Controller {
 public function __construct(){$this->guard();}
 public function edit():void{$this->index();}
 public function kuitansi():void{$m=new Penerimaan();$r=$m->getReceipt((int)($_GET['id']??0));if(!$r){http_response_code(404);die('Kuitansi tidak ditemukan.');}require __DIR__.'/../views/penerimaan/kuitansi.php';}
 public function index():void{$m=new Penerimaan();$err=null;$action=$_GET['action']??'index';$edit=null;$id=(int)($_GET['id']??0);
 if($action==='edit'){$edit=$m->getById($id);if(!$edit){flash('Data penerimaan tidak ditemukan.','err');redirect('index.php?page=penerimaan');}}
 if($_SERVER['REQUEST_METHOD']==='POST'){csrf_check();$postAction=$_POST['form_action']??'create';try{if($postAction==='delete'){$m->delete((int)($_POST['id']??0));flash('Penerimaan dan jurnal terkait berhasil dihapus.');redirect('index.php?page=penerimaan');}
 $baru=($_POST['donatur_mode']??'pilih')==='baru';$dd=['nama'=>trim($_POST['nama_donatur']??''),'jenis'=>$_POST['jenis_donatur']??'individu','telepon'=>trim($_POST['telepon_donatur']??''),'alamat'=>trim($_POST['alamat_donatur']??'')];$d=['tanggal'=>trim($_POST['tanggal']??''),'donatur_id'=>(int)($_POST['donatur_id']??0),'kategori_id'=>(int)($_POST['kategori_id']??0),'nominal'=>(float)($_POST['nominal']??0),'keterangan'=>trim($_POST['keterangan']??'')];
 if(!tanggal_valid($d['tanggal']))throw new RuntimeException('Tanggal penerimaan tidak valid.');if($baru){if($dd['nama']==='')throw new RuntimeException('Nama donatur baru wajib diisi.');$d['donatur_id']=(new Donatur())->create($dd);}if($d['donatur_id']<1)throw new RuntimeException('Pilih donatur atau tambah donatur baru.');if($d['kategori_id']<1)throw new RuntimeException('Kategori dana wajib dipilih.');if($d['nominal']<=0||$d['nominal']>=1e13)throw new RuntimeException('Nominal harus lebih dari 0.');if(mb_strlen($d['keterangan'])>255)throw new RuntimeException('Keterangan maksimal 255 karakter.');
 if($postAction==='update'){$m->update((int)($_POST['id']??0),$d);flash('Penerimaan dan jurnal berhasil diperbarui.');}else{$m->create($d);flash('Penerimaan dan jurnal berhasil disimpan.');}redirect('index.php?page=penerimaan');
 }catch(Throwable $e){$err=$e instanceof RuntimeException?$e->getMessage():'Gagal menyimpan data penerimaan.';if(!$edit&&($_POST['form_action']??'')==='update')$edit=$m->getById((int)($_POST['id']??0));}}
 $this->view('penerimaan/index',['rows'=>$m->getAll(),'donatur'=>$m->getDonatur(),'kategori'=>$m->getKategori(),'err'=>$err,'edit'=>$edit,'title'=>'Penerimaan Donasi']);}
}
