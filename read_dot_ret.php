<html>
<head>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.0/css/bootstrap.min.css" integrity="sha384-9gVQ4dYFwwWSjIDZnLEWnxCjeSWFphJiwGPXr1jddIhOegiu1FwO5qRGvFXOdJZ4" crossorigin="anonymous">
</head>
	<body>

			<nav class="navbar navbar-expand-lg navbar-light bg-light">
	      	Leitor de Arquivo .RET
	    </nav>
			<div class="container">
				<div class="col-md-12">
					<p>Selecione o arquivo:</p>
					<form name="frm" action="read_dot_ret.php" method="POST" enctype="multipart/form-data">
						<input type="file" name="file" required>
						<input type="submit" name="process" value="Processar" class="btn btn-primary">
					</form>
				</div>
			</div>

	</body>
</html>
<?php

	if(isset($_POST)):

    $mensagem = 'Selecione o arquivo .ret ';
    $dir = "tmp/"; 		//pasta temporaria para armazenar os arquivos
    $ext = array("ret");	//arquivos permitidos

		if(isset($_FILES) && !empty($_FILES)){
			echo "<pre>";
			// CREATE HEADER AND FOOTER TABLE
			$table = "<table class='table table-striped'>
									<thead class='thead-light'>
										<tr>
											<th>Cod Cedente</th>
											<th>Nosso Nro</th>
											<th>Dt. Vencimento</th>
											<th>vlr documento</th>
											<th>vlr Pgto</th>
											<th>vlr Creditado</th>
											<th>Dt. ocorrencia</th>
											<th>Dt. credito</th>
											<th>Dt. sacado</th>
										</tr>
									</thead>
									<tfoot class='thead-light'>
										<tr>
											<th>Cod Cedente</th>
											<th>Nosso Nro</th>
											<th>Dt. Vencimento</th>
											<th>vlr documento</th>
											<th>vlr Pgto</th>
											<th>vlr Creditado</th>
											<th>Dt. ocorrencia</th>
											<th>Dt. credito</th>
											<th>Dt. sacado</th>
										</tr>
									</tfoot>
									";

			foreach ($_FILES as $file) {

				$file_name 	= $file['name'];
				$file_tmp 	= $file['tmp_name'];
				$file_type 	= $file['type'];
				$file_ext 	= mb_strtolower(end(explode('.',$file_name)));

				if($file_ext != "ret"){
					die('.ret file extension not found');
				}

				if(!is_dir($dir)){
					mkdir($dir, 0775, true);
				}

				if (!(copy($file_tmp, $dir.$file_name))) {
					die('can not move file to tmp dir'.$dir.$file_name);
				}

				$file_open = fopen($dir.$file_name, "r");

	      while (!feof($file_open)):

	          $ln = fgets($file_open);

	          $operador = strtoupper(substr($ln, 13, 1));
	          $ocorrencia = strtoupper(substr($ln, 15, 2));
						$nosso_numero = substr($ln, 39, 18);

						$flag_retorno = strtoupper(substr($ln, 7, 1));

						if(empty($array_nosso_numero) OR (!(in_array($nosso_numero, $array_nosso_numero))) && $flag_retorno == "3" ){


						  if ($operador == "T" && $ocorrencia == "06" && $flag_retorno == "3") {

                  $codigo_cedente = substr($ln, 23, 6);
									$data_vencimento = substr($ln, 73, 2)."/".substr($ln, 75, 2)."/".substr($ln, 77, 4);
									$valor_titulo = substr($ln, 81, 15) / 100;

									$table .= "<tr>
														 <td>$codigo_cedente</td>
														 <td>$nosso_numero</td>
														 <td>$data_vencimento</td>
														 <td>$valor_titulo</td>";

              }

              if ($operador == "U" && $ocorrencia == "06" && $flag_retorno == "3") {

									$aux_valor = substr($ln, 77, 15);
                  @settype($aux_valor, float);
                  $valor_pago = $aux_valor / 100;

									$valor_desconto = ( substr($ln, 32, 15) ) / 100;
									$valor_juros = ( substr($ln, 17, 15) ) / 100;
									$valor_multa = ( substr($ln, 122, 15) )  / 100;

                  $aux_valor = substr($ln, 92, 15);
                  @settype($aux_valor, float);
                  $valor_creditado = $aux_valor / 100;

                  $data_ocorrencia = substr($ln, 137, 2)."/".substr($ln, 139, 2)."/".substr($ln, 141, 4);
                  $data_credito = substr($ln, 145, 2)."/".substr($ln, 147, 2)."/".substr($ln, 149, 4);
                  $data_sacado = substr($ln, 157, 2)."/".substr($ln, 159, 2)."/".substr($ln, 161, 4);

									$table .= "<td><b>PAGO = $valor_pago</b><br> Desconto: $valor_desconto | Juro: $valor_juros | Multa: $valor_multa </td>
														 <td>$valor_creditado</td>
														 <td>$data_ocorrencia</td>
														 <td>$data_credito</td>
														 <td>$data_sacado</td>
														 </tr>";

              }

					}

	    	endwhile;

			}

			echo $table;

		}


	endif;
?>
