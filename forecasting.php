<?php  
	session_start();
	require "function.php";
	if (!isset($_SESSION['login'])) {
		header("location:login.php");
		exit;
	}
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" type="text/css" href="style.css">
	<link href="https://fonts.googleapis.com/css2?family=Quicksand&display=swap" rel="stylesheet">
	<link rel="stylesheet" type="text/css" href="font/css/all.css">
	<title> Forecasting </title>
</head>
<body>
	<!-- header -->
	<header>
		<div class="container">
			<h1><a href=""> Kelompok 6 </h1>
			<ul>
				<li><a href="index.php"> Dashboard </a></li>
				<li><a href="data-aktual.php"> Data Aktual </a></li>							
				<li><a href="forecasting.php"> Forecasting </a></li>								
				<li><a href="hasil-forecasting.php"> Hasil Forecasting </a></li>				
				<li><a href="user-profil.php"> Profil </a></li>
				<li><a href="logout.php"> Logout </a></li>
			</ul>
		</div>
	</header>

<!-- content -->
	<div class="section">
		<div class="container">
			<h3>Hasil Forecasting</h3>
			<div class="box">

				<h4> Nilai Alpha </h4><br>
				<form method="post" action="">
					<select name="n_alpha" class="input-control-2" required>
						<option value="">~ Pilih Nilai Alpha ~</option>
						<option value="0.1">0,1</option>
						<option value="0.2">0,2</option>
						<option value="0.3">0,3</option>
						<option value="0.4">0,4</option>
						<option value="0.5">0,5</option>
						<option value="0.6">0,6</option>
						<option value="0.7">0,7</option>
						<option value="0.8">0,8</option>
						<option value="0.9">0,9</option>
					</select>
					<button type="submit" name="submit" class="hitung"> Hitung </button>
				</form>

				<br><hr><br>

				<?php
					if(isset($_POST['submit'])){
						$alpha = $_POST['n_alpha'];
				?>
					<h4> Menghitung Nilai Forecasting, MSE, dan MAPE (α = <?php echo $alpha?>)</h4><br>
					<table border="1" cellspacing="0" class="table">
						<thead>
							<tr>
								<th> No </th>
								<th> Tanggal </th>
								<th> Harga Emas (Y) </th>
								<th> Y' </th>
								<th> Y" </th>
								<th> A </th>
								<th> B </th>
								<th> Forecasting (Ŷ)</th>
								<th> MSE </th>
								<th> MAPE </th>
							</tr>
						</thead>
						<tbody style="text-align: center;">
						<?php
							$i = 1;
							$d_perkiraan = "";
							$d_perkiraan1 = "";
							$a = "";
							$b = "";
							$query = mysqli_query($conn, "SELECT * FROM harga_emas");
							$n = mysqli_num_rows($query);
							$n_data = $n-1;

							$total_eror = 0;
							$total_mape = 0;
							$total_mse = 0;

							while($tampil = mysqli_fetch_assoc($query)) {
								$harga = $tampil['harga'];
								$tanggal = $tampil['tanggal'];

								//forecasting pertama
								if($d_perkiraan === ""){
									$d_perkiraan = $harga;
									$d_perkiraan1 = $harga;
									$a = $harga;
									$b = 0;

									if($b == 0){
										$d_forecasting = '';
									}

								} else {
									$h_perkiraan = ($alpha*$harga)+(1-$alpha)*$d_perkiraan;
									$h_perkiraan1 = ($alpha*$h_perkiraan)+(1-$alpha)*$d_perkiraan1;
									$h_a = (2*$h_perkiraan)-$h_perkiraan1;
									$h_b = ($alpha/(1-$alpha))*($h_perkiraan-$h_perkiraan1);

									$d_forecasting = $a+($b*1);

									$d_perkiraan = $h_perkiraan;
									$d_perkiraan1 = $h_perkiraan1;

									$a = $h_a;
									$b = $h_b;
								}

								$array_forecasting[] = $d_forecasting;

								if($d_forecasting == ''){
									$ttl_eror = '';
									$ttl_mse = '';
									$ttl_mape = '';
								}else{
									$ttl_eror = $harga - $d_forecasting;
									$total_eror = $total_eror + $ttl_eror;
									$ttl_mse = $ttl_eror**2;
									$total_mse = $total_mse + $ttl_mse;
									$ttl_mape = abs($harga-$d_forecasting)/$harga;
									$total_mape = $total_mape + $ttl_mape;
								}
						?>

							<tr>
								<td><?php echo $i; ?></td>
								<td><?php echo $tanggal; ?></td>
								<td><?php echo number_format($harga);?></td>
								<td> <?php echo round($d_perkiraan,3); ?></td>
								<td> <?php echo round($d_perkiraan1,3);;?></td>
								<td> <?php echo round($a,3);?></td>
								<td> <?php echo round($b,3);?></td>
								<td> <?php echo round($d_forecasting,3);?></td>
								<td> <?php echo round($ttl_mse,3);?></td>
								<td> <?php echo round($ttl_mape,5); ?></td>
							</tr>

						<?php
							$i++;
							}
						?>

							<tr>
								<td colspan="8">Jumlah</td>
								<td><?=round($total_mse,4)?></td>
								<td><?=round($total_mape,4)?></td>
							</tr>			        
						</tbody>
					</table>

					<?php
						$a_akhir = $a;
						$b_akhir = $b;					
						$mse_akhir = $total_mse/$n_data;
						$mape_akhir = $total_mape*100/$n_data;
					?>
					<div class="hitung-mse-mape">
						<h3>
							MSE  = <?php echo $total_mse." / ".$n_data." = ".round($mse_akhir,3);?><br>
							MAPE = <?php echo $total_mape." / ".$n_data." = ".round($mape_akhir,4)?>
						</h3>
					</div>

				<?php
					}
				?>

			</div>
		</div>
	</div>

	<!-- footer -->
	<footer>
		<div class="container">
			<small> Copyright &copy; Forecasting Kelompok 6</small>
		</div>
	</footer>
</body>
</html>