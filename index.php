<!-- HEAD -- scripts and css -->
<meta name='viewport' content='width=device-width, initial-scale=1, shrink-to-fit=no'>
<link rel='stylesheet' href='https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css' crossorigin='anonymous'>
<link rel='stylesheet' href='custom.css'>
<link href="https://fonts.googleapis.com/css?family=Montserrat" rel="stylesheet">
<script src='https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js'></script>
<script src='https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js' crossorigin='anonymous'></script>
<script src='custom.js'></script>

<?php
function escapeChars($str){
	$str = str_replace(" ", "\ ", $str);
	$str = str_replace("(", "\(", $str);
	$str = str_replace(")", "\)", $str);
	$str = str_replace("[", "\[", $str);
	$str = str_replace("]", "\]", $str);
	return $str;
}

$sshHost = "localhost";
$sshPort = "22";
$sshUser = "root";
$sshPassword = " root password here ;) ";
$contentDir = "/data/videoFolder/";
$mainCommand = "sshpass -p '$sshPassword' ssh -o StrictHostKeyChecking=no $sshUser@$sshHost -p $sshPort";

$webtitle = "PopcorNow!";

$buffer;
exec("find $contentDir -name *.mkv -o -name *.avi -o -name *.mp4 -o -name *.flv -o -name *.webm -o -name *.m4v | sort", $buffer);

//Load logs
if (isset($_GET['log'])){
	$logData;
	exec("cat /data/logPHPApp.log", $logData);

	print("<div class='logging'><b>Logging: /data/logPHPApp.log </b><br/><br/>");
	for($i = 0; $i < sizeof($logData); $i++){
		print($logData[$i]);
		print("<br/>");
	}

	print('<a href="index.php" class="btn btn-primary"><span class="glyphicon glyphicon-repeat"></span></a></div>');

	return;
}

//Reboot
if (isset($_GET['reboot'])){
	exec("$mainCommand 'reboot &'");
	header('Location: index.php');
}

//Stop stream
if (isset($_GET['stop'])){
	exec("$mainCommand 'killall /opt/nodejs/bin/node > /dev/null &'");
	header("Location: index.php");
}

//Delete one to many files
if (isset($_GET['multidelete'])){
	if (isset($_GET['filter'])) {
		unset($buffer);
		$filter = $_GET['filter'];
		$filter = str_replace(" ", "\ ", $filter);
		
		exec("find $contentDir -iname *$filter*.mkv -o -iname *$filter*.avi -o -iname *$filter*.mp4 | sort", $buffer);
	}
	
	$deleteIndex = explode(",", $_GET['multidelete']);
	
	for ($i = 0; $i < sizeof($deleteIndex); $i++){
		$data = $buffer[$deleteIndex[$i]];
		$contentDirectory = dirname($data) . "/";
		
		if ($contentDirectory == $contentDir) {
			$data = escapeChars($data);
			exec("$mainCommand 'rm -rf $data' 2> /data/logPHPApp.log");
		} else {
			$contentDirectory = escapeChars($contentDirectory);
			exec("$mainCommand 'rm -rf $contentDirectory' 2> /data/logPHPApp.log");
		}
	}
	
	header('Location: index.php');
}

//Start to stream
if (isset($_GET['cast'])){
	if (isset($_GET['filter'])) {
		unset($buffer);
		$filter = $_GET['filter'];
		$filter = str_replace(" ", "\ ", $filter);
		
		exec("find $contentDir -iname *$filter*.mkv -o -iname *$filter*.avi -o -iname *$filter*.mp4 | sort", $buffer);
	}
	
	$cmd = $buffer[$_GET['cast']];
	$cmd = escapeChars($cmd);
	$seekTime = isset($_GET['seekTime']) ? $_GET['seekTime'] : '00:00:00';
	$addedParam = stripos($cmd, ".avi") === false ? '' : '--tomp4';
	
	exec("$mainCommand 'killall /opt/nodejs/bin/node' > /dev/null 2> /dev/null");
	exec("$mainCommand '/opt/nodejs/bin/node /opt/nodejs/lib/node_modules/castnow/index.js $cmd $addedParam --quiet --seek $seekTime' > /dev/null 2> /data/logPHPApp.log &");
	header('Location: index.php');
}

if (isset($_GET['filter'])) {
	unset($buffer);
	$filter = $_GET['filter'];
	$filter = str_replace(" ", "\ ", $filter);
	
	exec("find $contentDir -iname *$filter*.mkv -o -iname *$filter*.avi -o -iname *$filter*.mp4 | sort", $buffer);
}

$buffer = str_replace($contentDir,'',$buffer);

?>

<title><?php echo $webtitle; ?></title>

<!-- Panel -->
<div class="panel panel-default">
	<div class="panel-heading">
		<div class="panel-title">
			<img src="popcorn.png" width="150">
			<h1><?php echo $webtitle; ?></h1>
			<div class="botonera-izquierda">
				<a href="index.php?reboot=yes" class="btn btn-primary"><span class="glyphicon glyphicon-off"></span></a>
				<a href="index.php?log=yes" class="btn btn-primary"><span class="glyphicon glyphicon-list-alt"></span></a>
			</div>
			<div class="botonera-derecha">
				<a href="index.php" class="btn btn-primary"><span class="glyphicon glyphicon-repeat"></span></a>
				<a href="index.php?stop=yes" class="btn btn-primary"><span class="glyphicon glyphicon-stop"></span></a>
				<a href="#" onclick="multiDeleteMedia();" class="btn btn-primary"><span class="glyphicon glyphicon-trash"></span></a>
			</div>
		</div>
	</div>
	
	<div class="panel-body">
		<div class="buscador">
			<div class="row">
				<div class="col-md-11 col-xs-9">
					<input type="text" class="form-control" id="textoBuscador" placeholder="Buscar...">
				</div>
				<div class="col-md-1 col-xs-3">
					<a href="#" onclick="filterContent();" class="form-control btn btn-primary"><span class="glyphicon glyphicon-search"></span></a>
				</div>
			</div>
		</div>
<?php
	$i = 0;
	while($i < sizeof($buffer)){
?>
		<div class="row">
			<div class="col-md-3 col-xs-12">
				<div class="thumbnail">
					<div class="caption">
						<?php echo "<a class='media$i list-group-item' href='#' onclick='castMedia($i);'>$buffer[$i]</a>"; ?>
						<div class="row">
							<div class="col-xs-9">
								<input type="time" value="00:00" class="seekInput">
							</div>
							<div class="col-xs-3">
								<input type="checkbox" class="deleteChk">
							</div>
						</div>
					</div>
				</div>
			</div>
			<?php if (++$i == sizeof($buffer)) continue; ?>
			<div class="col-md-3 col-xs-12">
				<div class="thumbnail">
					<div class="caption">
						<?php echo "<a class='media$i list-group-item' href='#' onclick='castMedia($i);'>$buffer[$i]</a>"; ?>
						<div class="row">
							<div class="col-xs-9">
								<input type="time" value="00:00" class="seekInput">
							</div>
							<div class="col-xs-3">
								<input type="checkbox" class="deleteChk">
							</div>
						</div>
					</div>
				</div>
			</div>
			<?php if (++$i == sizeof($buffer)) continue; ?>
			<div class="col-md-3 col-xs-12">
				<div class="thumbnail">
					<div class="caption">
						<?php echo "<a class='media$i list-group-item' href='#' onclick='castMedia($i);'>$buffer[$i]</a>"; ?>
						<div class="row">
							<div class="col-xs-9">
								<input type="time" value="00:00" class="seekInput">
							</div>
							<div class="col-xs-3">
								<input type="checkbox" class="deleteChk">
							</div>
						</div>
					</div>
				</div>
			</div>
			<?php if (++$i == sizeof($buffer)) continue; ?>
			<div class="col-md-3 col-xs-12">
				<div class="thumbnail">
					<div class="caption">
						<?php echo "<a class='media$i list-group-item' href='#' onclick='castMedia($i);'>$buffer[$i]</a>"; ?>
						<div class="row">
							<div class="col-xs-9">
								<input type="time" value="00:00" class="seekInput">
							</div>
							<div class="col-xs-3">
								<input type="checkbox" class="deleteChk">
							</div>
						</div>
					</div>
				</div>
			</div>
			<?php if (++$i == sizeof($buffer)) continue; ?>
		</div>
<?php
	}
?>
	</div>
</div>
