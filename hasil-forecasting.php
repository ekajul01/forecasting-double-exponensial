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
    <script src="js/Chart.js"></script>
	<title> Hasil Forecasting </title>
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
				<?php
					$alpha = 0.3;
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
				<br><br><br><br>

				<br><hr><br>

				<h4> Grafik Data Aktual dan Forecasting </h4><br>
				
        		<canvas id="linechart" width="300" height="300"></canvas>

        		<br><hr><br>

				<h3>Forecasting Harga Emas 10 Hari Ke Depan</h3><br>
				<?php
					$query0 = mysqli_query ($conn, "SELECT * FROM harga_emas ORDER BY id DESC LIMIT 1");
					$tampil = mysqli_fetch_array($query0);
					$simpan1 = $tampil['tanggal'];
				?>
				Nilai A dan Nilai B yang digunakan adalah data dari tanggal <?php echo $simpan1;?> <br>
				Nilai A = <?php echo $a_akhir;?> <br>
				Nilai B = <?php echo $b_akhir;?> <br>
				Ŷ =  <?php echo $a_akhir;?> + (<?php echo $b_akhir;?> * m) <br><br>
				
				<table border="1" cellspacing="0" class="table">
					<thead>
						<tr>
							<th> No </th>
							<th> Tanggal </th>
							<th> Jangka Waktu (m) </th>
							<th> Forecasting (Ŷ)</th>
						</tr>
					</thead>
					<tbody style="text-align: center;">
					<?php
						$m = 1;

						$tanggal1 = substr($simpan1,0,2);	                
		                $bln = substr($simpan1,3,3);
						$tahun = substr($simpan1,7,4);

						if ($bln == 'Jan') {
							$bulan = 01;
						}elseif ($bln == 'Feb') {
							$bulan = 02;
						}elseif ($bln == 'Mar') {
							$bulan = 03;
						}elseif ($bln == 'Apr') {
							$bulan = 04;
						}elseif ($bln == 'Mei') {
							$bulan = 05;
						}elseif ($bln == 'Jun') {
							$bulan = 06;
						}elseif ($bln == 'Jul') {
							$bulan = 07;
						}elseif ($bln == 'Ags') {
							$bulan = 8;
						}elseif ($bln == 'Sep') {
							$bulan = 9;
						}elseif ($bln == 'Okt') {
							$bulan = 10;
						}elseif ($bln == 'Nov') {
							$bulan = 11;
						}else {
							$bulan = 12;
						}

						$tampiltgl = $tahun."-".$bulan."-".$tanggal1;

						$tgl2 = date('Y-m-d', strtotime('+1 days', strtotime($tampiltgl))); 
						$query1 = mysqli_query ($conn, "SELECT * FROM harga_emas");
						while ($m < 11) {
							$forecast= $a_akhir + ($b_akhir*$m);
					?>

						<tr>
							<td><?php echo $m; ?></td>

							<?php
								$tahunx = substr($tgl2,0,4);
								$blnx = substr($tgl2,5,2);
								$tanggalx = substr($tgl2,8,2);

								if ($blnx == 01) {
									$bulanx = 'Jan';
								}elseif ($blnx == 02) {
									$bulanx = 'Feb';
								}elseif ($blnx == 03) {
									$bulanx = 'Mar';
								}elseif ($blnx == 04) {
									$bulanx = 'Apr';
								}elseif ($blnx == 05) {
									$bulanx = 'Mei';
								}elseif ($blnx == 06) {
									$bulanx = 'Jun';
								}elseif ($blnx == 07) {
									$bulanx = 'Jul';
								}elseif ($blnx == 8) {
									$bulanx = 'Ags';
								}elseif ($blnx == 9) {
									$bulanx = 'Sep';
								}elseif ($blnx == 10) {
									$bulanx = 'Okt';
								}elseif ($blnx == 11) {
									$bulanx = 'Nov';
								}else {
									$bulanx = 'Des';
								}

								$tgl2x = $tanggalx." ".$bulanx." ".$tahunx;
							?>

							<td><?php echo $tgl2x; ?></td>
							<td><?php echo $m; ?></td>
							<td><?php echo round($forecast,3); ?></td>
					</tr>

					<?php
						$m++;
						$tgl2++;
						}
					?>		        
					</tbody>
				</table>

				<form method="post" action="">
					Peramalan <input type="number" name="jangka" min="1" class="input-control-2"> hari ke depan
				<button type="submit" name="hitung" class="hitung"> Hitung </button>	
				</form>

				<?php 
					if(isset($_POST['hitung'])){
						$jangka = $_POST['jangka'];
						$forecastbaru = $a_akhir + ($b_akhir*$jangka);
						echo "Forecasting harga emas $jangka hari ke depan adalah Rp. $forecastbaru <br>";
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

<script  type="text/javascript">
  	var ctx = document.getElementById("linechart").getContext("2d");
  	var data = {
        labels: [ <?php 
                    $query = mysqli_query($conn, "SELECT tanggal FROM harga_emas");
                    while($tgl = mysqli_fetch_array($query)){
                        echo "\"$tgl[tanggal]\", ";;
                    }
                  ?>
                ],
        datasets:  [
                {
                    label: "Aktual",
                    fill: false,
                    lineTension: 0.1,
                    backgroundColor: "#29B0D0",
                    borderColor: "#29B0D0",
                    pointHoverBackgroundColor: "#29B0D0",
                    pointHoverBorderColor: "#29B0D0",
                    data: [<?php 
                      	$aktual = mysqli_query($conn, "SELECT harga FROM harga_emas");
                      	while ($data_aktual = mysqli_fetch_array($aktual)) {
                        	$d_akt = $data_aktual['harga'];
                        	echo $d_akt.',';
                      	}
                          ?>]
                },
                {
                    label: "Forecasting",
                    fill: false,
                    lineTension: 0.1,
                    backgroundColor: "#2A516E",
                    borderColor: "#2A516E",
                    pointHoverBackgroundColor: "#2A516E",
                    pointHoverBorderColor: "#2A516E",
                    data: [<?php 
                      	foreach ($array_forecasting as $arfor) {
                        	echo "".$arfor.", ";
                      	}
                          ?>]
                }
                   ]
          };

    var myBarChart = new Chart(ctx, {
        type: 'line',
        data: data,
        options: {
        	legend: {
              	display: true
            },
            barValueSpacing: 20,
            scales: {
              	yAxes: [{
                  	ticks: {
                      	min: 0,
                  	}
              	}],
              	xAxes: [{
                    gridLines: {
                        color: "rgba(0, 0, 0, 0)",
                    }
                      }]
            }
        }
    });
</script>