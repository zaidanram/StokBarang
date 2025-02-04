<?php
 session_start();

// Konfigurasi Database
$host       = 'localhost'; // host
$username   = 'root'; // username database
$password   = ''; // password database
$dbname     = 'stockbarang'; // nama database
 
$db = mysqli_connect($host, $username, $password, $dbname);
 
//menambah barang baru

if(isset($_POST['addNewBarang'])){
	$namabarang = $_POST['namabarang'];
	$deskripsi = $_POST['deskripsi'];
	$stock = $_POST['stock'];

	//soal gambar
	$allowed_extension = array('png','jpg');
	$nama =$_FILES['file']['name']; //ngambil nama gambar
	$dot = explode('.', $nama);
	$ekstensi = strtolower(end($dot)); // ngambil extensinya
	$ukuran =$_FILES['file']['size']; // ngambil file sizenya
	$file_tmp = $_FILES['file']['tmp_name']; //ngambil lokasi file

	//penamaan file ->enkripsi
	$image = md5(uniqid($nama,true).time()).'.'.ekstensi; //menggabungkan nama file yang dienskripsi dengan extensinya.



	//validasi udah ada atau belum
	$cek = mysqli_query($db,"select * from stock where namabarang ='$namabarang'");
	$hitung = mysqli_num_rows($cek);


	if($hitung <1) {
		// jika belum ada

		//proses upload gambar
		if (in_array($ekstensi, $allowed_extension) === true) {
			// validasi ukuran file
			if($ukuran < 150000000){
				move_uploaded_file($file_tmp, 'images/'.$image);

				$addtotable = mysqli_query($db,"insert into stock (namabarang, deskripsi, stock,image) values('$namabarang','$deskripsi','$stock','$image') ");

				if($addtotable){
					header('location:index.php');
				}else{
					echo 'gagal';
				header('location:index.php');
			}

			}else{
				//kalau filenya lebih dari 15 mb
				echo'
				<script>
					alert("ukuran terlalu besar");
					window.location.href = "index.php";
					</script>
					';
			}
		} else{
			//kalo filenya tidak png/png
			echo'
			<script>
				alert("File harus jpg/png");
				window.location.href = "index.php";
			</script>
		';
		}
			
} else{
	//jika sudah ada
	echo'
	<script>
		alert("Nama barang sudah terdaftar");
		window.location.href = "index.php";
		</script>
		';
}
};



//menambah barang masuk
if(isset($_POST['barangmasuk'])){
	$barangnya = $_POST['barangnya'];
	$penerima = $_POST['penerima'];
	$qty = $_POST['qty'];

	$cekstocksekarang = mysqli_query($db,"select * from stock where idbarang ='$barangnya'");
	$ambildatanya = mysqli_fetch_array($cekstocksekarang);

	$stocksekarang = $ambildatanya['stock'];
	$tambahkanstocksekarangdenganquantity = $stocksekarang + $qty;

	$addtomasuk = mysqli_query($db,"insert into masuk(idbarang,keterangan,qty) values ('$barangnya','$penerima','$qty')");
	$updatestockmasuk = mysqli_query($db,"update stock set stock='$tambahkanstocksekarangdenganquantity'where idbarang ='$barangnya'");
if($addtomasuk&&$updatestockmasuk){
		header('location:masuk.php');
	}else{
		echo 'gagal';
		header('location:masuk.php');
	}




}
//menambah barang keluar
if(isset($_POST['addbarangkeluar'])){
	$barangnya = $_POST['barangnya'];
	$penerima = $_POST['penerima'];
	$qty = $_POST['qty'];

	$cekstocksekarang = mysqli_query($db,"select * from stock where idbarang ='$barangnya'");
	$ambildatanya = mysqli_fetch_array($cekstocksekarang);

	$stocksekarang = $ambildatanya['stock'];

	if($stocksekarang >= $qty){
		//kalau barang cukup
		$tambahkanstocksekarangdenganquantity = $stocksekarang-$qty;

		$addtokeluar = mysqli_query($db,"insert into keluar (idbarang,penerima,qty) values ('$barangnya','$penerima','$qty')");
		$updatestockmasuk = mysqli_query($db,"update stock set stock='$tambahkanstocksekarangdenganquantity'where idbarang ='$barangnya'");
		if($addtokeluar&&$updatestockmasuk){
			header('location:keluar.php');
		}else{
			echo 'gagal';
			header('location:keluar.php');
		}
}else {
		//kalau barang tidak cukup
		echo '
		<script>
			alert("Stock saat ini tidak mencukupi");
			window.location.href="keluar.php";

		</script>
		';



}


}

//Update Info Barang
if(isset($_POST['updatebarang'])){

	$idb = $_POST['idb'];
	$namabarang = $_POST['namabarang'];
	$deskripsi = $_POST['deskripsi'];

	//soal gambar
	$allowed_extension = array('png','jpg');
	$nama =$_FILES['file']['name']; //ngambil nama gambar
	$dot = explode('.', $nama);
	$ekstensi = strtolower(end($dot)); // ngambil extensinya
	$ukuran =$_FILES['file']['size']; // ngambil file sizenya
	$file_tmp = $_FILES['file']['tmp_name']; //ngambil lokasi file

	//penamaan file ->enkripsi
	$image = md5(uniqid($nama,true).time()).'.'.ekstensi; //menggabungkan nama file yang dienskripsi dengan extensinya.

	if($ukuran ==0){
		//jika tidak ingin upload
		$update = mysqli_query($db,"update stock set namabarang='$namabarang', deskripsi='$deskripsi' where idbarang='$idb'");
		if($update){
			header('location:index.php');
		} else {
			echo 'Gagal';
			header('location:index.php');
		}

	} else{
		//jika ingin
		move_uploaded_file($file_tmp, 'images/'.$image);

		$update = mysqli_query($db,"update stock set namabarang='$namabarang', deskripsi='$deskripsi', image='$image' where idbarang='$idb'");
		if($update){
			header('location:index.php');

		} else{
			echo 'Gagal';
			header ('location:index.php');
		}

	}

}

//Menghapus barang dari stock

if(isset($_POST['hapusbarang'])){

	$idb = $_POST['idb'];//idbarang

	$gambar = mysqli_query($db,"select * from stock where idbarang ='$idb'");
	$get = mysqli_fetch_array($gambar);
	$img = 'images/'.$get['image'];
	unlink($img);

	$hapus = mysqli_query($db,"delete from stock where idbarang='$idb'");
	if($hapus){
		header('location:index.php');
	}else{
		echo 'gagal';
		header('location:index.php');


	}
};
//Mengubah data barang masuk
if(isset($_POST['updatebarangmasuk'])){

	$idb = $_POST['idb'];
	$idm = $_POST['idm'];

	$deskripsi = $_POST['keterangan'];
	$qty = $_POST['qty'];

	$lihatstock = mysqli_query($db,"select * from stock where idbarang='$idb'");
	$stocknya =mysqli_fetch_array($lihatstock);
	$stockskrg =$stocknya['stock'];

	$qtyskrg =mysqli_query($db,"select * from masuk where idmasuk='$idm'");
	$qtynya = mysqli_fetch_array($qtyskrg);
	$qtyskrg =$qtynya['qty'];


	if($qty>$qtyskrg){
		$selisih = $qty-$qtyskrg;
		$kurangin = $stockskrg-$selisih;
		$kurangistocknya = mysqli_query($db,"update stock set stock ='$kurangin'where idbarang='$idb'");
		$updatenya = mysqli_query($db,"update masuk set qty='$qty',keterangan ='$deskripsi' where idmasuk='$idm'");

	if($kurangistocknya&&$updatenya) {
		header('location:masuk.php');
	}else{
		echo 'gagal';
		header('location:masuk.php');
	}

	}else {
		$selisih = $qtyskrg-$qty;
		$kurangin = $stockskrg + $selisih;
		$kurangistocknya = mysqli_query($db,"update stock set stock ='$kurangin'where idbarang='$idb'");
		$updatenya = mysqli_query($db,"update masuk set qty='$qty',keterangan ='$deskripsi' where idmasuk='$idm'");
			if($kurangistocknya&&$updatenya){
				header('location:masuk.php');
				}else{
					echo 'gagal';
					header('location:masuk.php');
	}

	}
}

//Menghapus barang masuk

if(isset($_POST['hapusbarangmasuk'])){

	$idb = $_POST['idb'];
	$qty = $_POST['kty'];
	$idm = $_POST['idm'];

	$getdatastock = mysqli_query($db,"select * from stock where idbarang='$idb'");
	$data = mysqli_fetch_array($getdatastock);
	$stok =$data['stock'];

	$selisih = $stok-$qty;

	$update =mysqli_query($db,"update stock set stock ='$selisih' where idbarang ='$idb'");
	$hapusdata = mysqli_query($db,"delete from masuk where idmasuk='$idm'");

	if($update&&$hapusdata){
		header('location:masuk.php');

	}else{
		header('location:masuk.php');

	}

}

//Mengubah data barang keluar
if(isset($_POST['updatebarangkeluar'])){

	$idb = $_POST['idb'];
	$idk = $_POST['idk'];

	$penerima = $_POST['penerima'];
	$qty = $_POST['qty'];

	$lihatstock = mysqli_query($db,"select * from stock where idbarang='$idb'");
	$stocknya =mysqli_fetch_array($lihatstock);
	$stockskrg =$stocknya['stock'];

	$qtyskrg =mysqli_query($db,"select * from keluar where idkeluar='$idk'");
	$qtynya = mysqli_fetch_array($qtyskrg);
	$qtyskrg =$qtynya['qty'];


	if($qty>$qtyskrg){
		$selisih = $qty-$qtyskrg;
		$kurangin = $stockskrg-$selisih;
		$kurangistocknya = mysqli_query($db,"update stock set stock ='$kurangin'where idbarang='$idb'");
		$updatenya = mysqli_query($db,"update keluar set qty='$qty',penerima ='$penerima' where idkeluar='$idk'");

	if($kurangistocknya&&$updatenya) {
		header('location:keluar.php');
	}else{
		echo 'gagal';
		header('location:keluar.php');
	}

	}else {
		$selisih = $qtyskrg-$qty;
		$kurangin = $stockskrg + $selisih;
		$kurangistocknya = mysqli_query($db,"update stock set stock ='$kurangin'where idbarang='$idb'");
		$updatenya = mysqli_query($db,"update keluar set qty='$qty',penerima ='$penerima' where idkeluar='$idk'");
			if($kurangistocknya&&$updatenya){
				header('location:keluar.php');
				}else{
					echo 'gagal';
					header('location:keluar.php');
	}

	}
}

//Menghapus barang keluar

if(isset($_POST['hapusbarangkeluar'])){

	$idb = $_POST['idb'];
	$qty = $_POST['kty'];
	$idk = $_POST['idk'];

	$getdatastock = mysqli_query($db,"select * from stock where idbarang='$idb'");
	$data = mysqli_fetch_array($getdatastock);
	$stok =$data['stock'];

	$selisih = $stok+$qty;

	$update =mysqli_query($db,"update stock set stock ='$selisih' where idbarang ='$idb'");
	$hapusdata = mysqli_query($db,"delete from keluar where idkeluar='$idk'");

	if($update&&$hapusdata){
		header('location:keluar.php');

	}else{
		header('location:keluar.php');

	}

}


//menambah admin baru
if(isset($_POST['addadmin'])){
	$email = $_POST['email'];
	$password = $_POST['password'];

	$queryinsert = mysqli_query($db,"insert into login (email,password) values ('$email','$password')");

	if ($queryinsert) {
		// if berhasil
		header('location:admin.php');

	} else {
		//kalau gagal insert ke db
		header('location:admin.php');
	}
}

	//edit data admin
	if(isset($_POST['updateadmin'])){
		$emailbaru = $_POST['emailadmin'];
		$passwordbaru= $_POST['passwordbaru'];
		$idnya = $_POST['id'];

		$queryupdate = mysqli_query($db,"update login set email ='$emailbaru',password ='$passwordbaru' where iduser ='$idnya' ");


		if($queryupdate){
		header('location:admin.php');

		} else{
			header('location:admin.php');

		}


	}

	//hapus admin
	if(isset($_POST['hapusadmin'])){

		$id = $_POST['id'];

		$querydelete = mysqli_query ($db,"delete from login where iduser ='$id'");


		if($querydelete){
		header('location:admin.php');

		} else{
			header('location:admin.php');

		}
	}

?>