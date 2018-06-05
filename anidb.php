<?php
require_once('./include/core.class.php');
require_once('./include/config.php');

header("Content-Type: text/html; charset=utf8;");
$core = new Core();
$core->InitDB(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, DB_TABLE);
if(!$core->isDatabaseExist($core->dbname))
{
	$core->createDatabase($core->dbname);
	$core->connectToDatabase();
	$core->writeDebug("Database " . $core->dbname . " successfully created");
	if(!$core->isTableExist())
	{
		$core->setQuery("CREATE TABLE $core->tablename (
						id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
						judul MEDIUMTEXT NOT NULL,			
						episode_total INT(6) UNSIGNED,
						episode_sekarang INT(6) UNSIGNED,
						status VARCHAR(64) NOT NULL,
						last_updated DATETIME NOT NULL
						) ENGINE = INNODB CHARSET = UTF8");
		$core->createTable();
		$core->writeDebug("Table " . $core->tablename . " successfully created");
		header( "Refresh:5; url=/anidb.php", true, 303);
		exit();
	}
}
else{
	$core->connectToDatabase();
	if(!$core->isTableExist())
	{
		$core->setQuery("CREATE TABLE $core->tablename (
						id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
						judul MEDIUMTEXT NOT NULL,			
						episode_total INT(6) UNSIGNED,
						episode_sekarang INT(6) UNSIGNED,
						status VARCHAR(64) NOT NULL,
						last_updated DATETIME NOT NULL
						) ENGINE = INNODB CHARSET = UTF8");
		$core->createTable();
		$core->writeDebug("Table " . $core->tablename . " successfully created");
		header( "Refresh:5; url=/anidb.php", true, 303);
		exit();
	}
}

if(isset($_POST["ssubmit"]))
{
	$judul = mysqli_real_escape_string($core->conn, htmlentities($_POST['judulanime'], ENT_QUOTES, 'UTF-8'));
	$eptotal = mysqli_real_escape_string($core->conn, $_POST["episodetotal"]);
	$epsekarang = mysqli_real_escape_string($core->conn, $_POST["episodesekarang"]);
	//$status = mysqli_real_escape_string($core->conn, $_POST["statusanime"]);
	$lastupdate = date("Y-m-d H:i:s");
	//$judula = htmlspecialchars($judul);
	//check duplicate record
	$qry = "SELECT * FROM $core->tablename WHERE judul='$judul'";
	$res = mysqli_query($core->conn, $qry);
	$success = -1;
	if($eptotal < 0)
	{
		$eptotal = 0;
	}
	if($epsekarang < 0)
	{
		$epsekarang = 0;
	}
	if(!is_numeric($eptotal) || !is_numeric($epsekarang))
	{
		$success = 0;
		echo '<div class="alert alert-warning alert-dismissable auto-fade">
			 <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Some data that you have inserted is invalid..</div>';
	}
	else if(is_numeric($eptotal) && is_numeric($epsekarang))
	{
		if($eptotal > $epsekarang && $eptotal != 0){
			$status = "Not yet";
		}
		else if($eptotal == $epsekarang && $eptotal != 0)
		{
			$status = "Done";
		}
		else if($eptotal == 0)
		{
			$status = "Undefined";
		}
		else
		{
			$status = "Undefined";
		}
		if(mysqli_num_rows($res) <= 0)
		{
			//ngecheck id terakhir
			//mysqli_query($core->conn, "ALTER TABLE $core->tablename AUTO_INCREMENT = ( SELECT max(id)+1 FROM $core->tablename)");
			$query = "INSERT INTO $core->tablename VALUES ('', '$judul', '$eptotal', '$epsekarang', '$status', '$lastupdate')";
			$result = mysqli_query($core->conn, $query);
			//unset($_POST["ssubmit"]);
			if(!$result){
				echo mysqli_error($core->conn);
				$success = 2;
			}
			else{
				$success = 3;
			}
		}
		else
		{
			$success = 1;
		}
	}
}

if(isset($_POST["usubmit"]))
{
	$judul = mysqli_real_escape_string($core->conn, htmlentities($_POST["judulanimeupdate"], ENT_QUOTES, 'UTF-8'));
	$jdlhid = mysqli_real_escape_string($core->conn, $_POST["jdlhid"]);
	$eptotal = mysqli_real_escape_string($core->conn, $_POST["episodetotalupdate"]);
	$epsekarang = mysqli_real_escape_string($core->conn, $_POST["episodesekarangupdate"]);
	//$status = mysqli_real_escape_string($core->conn, $_POST["statusanimeupdate"]);
	$lastupdate = date("Y-m-d H:i:s");
	$success = -1;
	if($eptotal < 0)
	{
		$eptotal = 0;
	}
	if($epsekarang < 0)
	{
		$epsekarang = 0;
	}
	if(!is_numeric($eptotal) || !is_numeric($epsekarang))
	{
		$success = 0;
		/*echo '<div class="alert alert-warning alert-dismissable auto-fade">
			<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Some data that you have inserted is invalid..</div>';*/
	}
	else if(is_numeric($eptotal) && is_numeric($epsekarang))
	{
		if($eptotal > $epsekarang && $eptotal != 0){
			$status = "Not yet";
		}
		else if($eptotal == $epsekarang && $eptotal != 0)
		{
			$status = "Done";
		}
		else if($eptotal == 0)
		{
			$status = "Undefined";
		}
		else{
			$status = "Undefined";
		}
		$query = "UPDATE $core->tablename SET judul='$judul', episode_total='$eptotal', episode_sekarang='$epsekarang', status='$status', last_updated='$lastupdate' WHERE id='$jdlhid'";
		$result = mysqli_query($core->conn, $query);
		if(!$result){
			echo mysqli_error($core->conn);
			$success = 1;
		}
		else{
			//unset($_POST["usubmit"]);
			$success = 2;
			/*echo'<div class="alert alert-warning alert-dismissable auto-fade">
			<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Data successfully updated</div>';*/
		}
	}							
}

if(isset($_POST["dsubmit"]))
{
	$judul = mysqli_real_escape_string($core->conn, htmlentities($_POST["judulanimedelete"], ENT_QUOTES, 'UTF-8'));
	//$judula = htmlspecialchars($judul);
	$query = "DELETE FROM $core->tablename WHERE judul='$judul'";
	$result = mysqli_query($core->conn, $query);
	$success = -1;
	if(!$result){
		echo mysqli_error($core->conn);
		$success = 0;
	}
	else{
		$success = 1;
		/*unset($_POST["dsubmit"]);
		echo'<div class="alert alert-warning alert-dismissable auto-fade">
		<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Successfully delete the data</div>';
		if(empty($_SERVER["QUERY_STRING"]) && !isset($_GET["action"]))
		{
			$core->redirectToURL("/anidb.php?action=delete");
		}
		else if(!empty($_SERVER["QUERY_STRING"]) && !isset($_GET["action"])){
			$core->redirectToURL("/anidb.php?".$_SERVER["QUERY_STRING"] ."&action=delete");
		}
		else{
			$core->redirectToURL("/anidb.php?".$_SERVER["QUERY_STRING"]);
		}*/
	}
}
?>

<!DOCTYPE html>
<html>
	<head>
		<title>AniDB Lists - Localhost</title>
		<link rel="stylesheet" href="./assets/css/bootstrap.min.css"/>
		<script type="text/javascript" src="./assets/js/jquery.min.js"></script>
		<script type="text/javascript" src="./assets/js/bootstrap.min.js"></script>
		<style>
			body{
				font-family:Roboto;
			}
			
			table #status{
				color:white;
				font-weight:Bold;
				
			}
			
			#table-1{
				margin-top:25px;
			}
		</style>
		<script>
		$.fn.pageMe = function(opts)
		{
		    var $this = this,
		        defaults = {
		            perPage: 7,
		            showPrevNext: false,
		            hidePageNumbers: false
		        },
		        settings = $.extend(defaults, opts);
		    
		    var listElement = $this;
		    var perPage = settings.perPage; 
		    var children = listElement.children();
		    var pager = $('.pager');
		    
		    if (typeof settings.childSelector!="undefined") {
		        children = listElement.find(settings.childSelector);
		    }
		    
		    if (typeof settings.pagerSelector!="undefined") {
		        pager = $(settings.pagerSelector);
		    }
		    
		    var numItems = children.size();
		    var numPages = Math.ceil(numItems/perPage);

		    pager.data("curr",0);
		    
		    if (settings.showPrevNext){
		        $('<li><a href="#" class="prev_link">«</a></li>').appendTo(pager);
		    }
		    
		    var curr = 0;
		    while(numPages > curr && (settings.hidePageNumbers==false)){
		        $('<li><a href="#" class="page_link">'+(curr+1)+'</a></li>').appendTo(pager);
		        curr++;
		    }
		    
		    if (settings.showPrevNext){
		        $('<li><a href="#" class="next_link">»</a></li>').appendTo(pager);
		    }
		    
		    pager.find('.page_link:first').addClass('active');
		    pager.find('.prev_link').hide();
		    if (numPages<=1) {
		        pager.find('.next_link').hide();
		    }
		  	pager.children().eq(1).addClass("active");
		    
		    children.hide();
		    children.slice(0, perPage).show();
		    
		    pager.find('li .page_link').click(function(){
		        var clickedPage = $(this).html().valueOf()-1;
		        goTo(clickedPage,perPage);
		        return false;
		    });
		    pager.find('li .prev_link').click(function(){
		        previous();
		        return false;
		    });
		    pager.find('li .next_link').click(function(){
		        next();
		        return false;
		    });
		    
		    function previous(){
		        var goToPage = parseInt(pager.data("curr")) - 1;
		        goTo(goToPage);
		    }
		     
		    function next(){
		        goToPage = parseInt(pager.data("curr")) + 1;
		        goTo(goToPage);
		    }
		    
		    function goTo(page){
		        var startAt = page * perPage,
		            endOn = startAt + perPage;
		        
		        children.css('display','none').slice(startAt, endOn).show();
		        
		        if (page>=1) {
		            pager.find('.prev_link').show();
		        }
		        else {
		            pager.find('.prev_link').hide();
		        }
		        
		        if (page<(numPages-1)) {
		            pager.find('.next_link').show();
		        }
		        else {
		            pager.find('.next_link').hide();
		        }
		        
		        pager.data("curr",page);
		      	pager.children().removeClass("active");
		        pager.children().eq(page+1).addClass("active");
		    
		    }
		};

		$(document).ready(function(){
		  $('#table-2').pageMe({pagerSelector:'#myPager',showPrevNext:true,hidePageNumbers:false,perPage:40});
		  
		  $("#uform").attr("action", writeParam("action", "update"));
		  $("#sform").attr("action", writeParam("action", "submit"));
		  $("#dform").attr("action", writeParam("action", "delete"));
		});

		/*$(document).ready(function() { 
		    $('#table-1').paging({
				limit: 30+1,
				rowDisplayStyle: 'block',
				activePage: 2,
				rows: []
			});
		}); */
		function GetURLParameter(sParam)
		{
			var sPageURL = window.location.search.substring(1);
			var sURLVariables = sPageURL.split('&');
			for (var i = 0; i < sURLVariables.length; i++)
			{
				var sParameterName = sURLVariables[i].split('=');
				if (sParameterName[0] == sParam)
				{
					return sParameterName[1];
				}
			}
		}
		
		function changeURLParameter(param, value)
		{
			/*
			 * queryParameters -> handles the query string parameters
			 * queryString -> the query string without the fist '?' character
			 * re -> the regular expression
			 * m -> holds the string matching the regular expression
			 */
			var queryParameters = {}, queryString = location.search.substring(1),
			    re = /([^&=]+)=([^&]*)/g, m;
			 
			// Creates a map with the query string parameters
			while (m = re.exec(queryString)) {
				queryParameters[decodeURIComponent(m[1])] = decodeURIComponent(m[2]);
			}
			
			// Add new parameters or update existing ones
			//queryParameters['newParameter'] = 'new parameter';
			queryParameters[param] = value;
			
			/*
			 * Replace the query portion of the URL.
			 * jQuery.param() -> create a serialized representation of an array or
			 *     object, suitable for use in a URL query string or Ajax request.
			 */
			location.search = $.param(queryParameters); // Causes page to reload
		}
		
		function writeParam(param, value)
		{
			var queryParameters = {}, queryString = location.search.substring(1),
			    re = /([^&=]+)=([^&]*)/g, m;
			 
			// Creates a map with the query string parameters
			while (m = re.exec(queryString)) {
				queryParameters[decodeURIComponent(m[1])] = decodeURIComponent(m[2]);
			}
			
			// Add new parameters or update existing ones
			//queryParameters['newParameter'] = 'new parameter';
			queryParameters[param] = value;
			return "?" + $.param(queryParameters);
		}

		/*$(document).ready(function(){
			$("#ssubmit").click(function(){
				var judul = $("#judulanime").val();
				var eptotal = $("#episodetotal").val();
				var epsekarang = $("#episodesekarang").val();
				$.post("proses.php", {
					action: "submit",
					judulanime: judul,
					episodetotal: eptotal,
					episodesekarang: epsekarang
				},
				function(data, status){
					alert("Data: "+data+" Status: " + status);
				});
			});
		});*/
		</script>
	</head>

	<body>
		<div class="container">
			<ul class="nav nav-tabs">
				<?php
					if(isset($_GET["action"]))
					{
						if($_GET["action"] == "update")
						{
							echo '<li><a class="nav-container" data-toggle="tab" href="#submit">Submit</a></li>
								  <li class="active"><a class="nav-container" data-toggle="tab" href="#update">Update</a></li>
								  <li><a class="nav-container" data-toggle="tab" href="#delete">Delete</a></li>';
						}
						else if($_GET["action"] == "delete")
						{
							echo '<li><a class="nav-container" data-toggle="tab" href="#submit">Submit</a></li>
								  <li><a class="nav-container" data-toggle="tab" href="#update">Update</a></li>
								  <li class="active"><a class="nav-container" data-toggle="tab" href="#delete">Delete</a></li>';
						}
						else{
							echo '<li class="active"><a class="nav-container" data-toggle="tab" href="#submit">Submit</a></li>
								  <li><a class="nav-container" data-toggle="tab" href="#update">Update</a></li>
								  <li><a class="nav-container" data-toggle="tab" href="#delete">Delete</a></li>';
						}
					}
					else
					{
						echo '<li class="active"><a class="nav-container" data-toggle="tab" href="#submit">Submit</a></li>
							  <li><a class="nav-container" data-toggle="tab" href="#update">Update</a></li>
							  <li><a class="nav-container" data-toggle="tab" href="#delete">Delete</a></li>';
					}
				?>
			</ul>
			<div class="tab-content">
			<div class="tab-pane form-horizontal fade <?php 
				if(isset($_GET["action"])) 
				{ 
					if($_GET["action"] == "submit")
					{
						echo "in active";
					}
				}
				else {
					echo "in active";
				}
				?>" id="submit">
				<form id="sform" name="sform" action="" method="post" style="margin-top:25px">
					<fieldset>
						<div class="form-group">
							<label class="control-table col-sm-2">Judul Anime</label>
							<div class="col-sm-10">
							<input type="text" name="judulanime" id="judulanime" placeholder="Judul Anime" class="required form-control" size="24" required>
							</div>
						</div>
						<div class="form-group">
							<label class="control-table col-sm-2">Episode Total</label>
							<div class="col-sm-10">
							<input type="number" name="episodetotal" id="episodetotal" placeholder="Episode Total" class="required form-control" size="24" data-toggle="popover" data-placement="right" required>
							</div>
						</div>
						<div class="form-group">
							<label class="control-table col-sm-2">Episode Sekarang</label>
							<div class="col-sm-10">
							<input type="number" name="episodesekarang" id="episodesekarang" placeholder="Episode yang sedang ditonton" class="required form-control" size="24" data-toggle="popover" data-placement="right" required>
							</div>
						</div>
						<!--<div class="form-group">
							<label class="control-table col-sm-2">Status</label>
							<div class="col-sm-10">
							<select name="statusanime" required>
								<option value=""> </option>
								<option value="Done">Done</option>
								<option value="Not yet">Not yet</option>
							</select>
							</div>
						</div>-->
						<?php
						if(isset($_POST["ssubmit"]))
						{
							if($success == 0)
							{
								echo '<div class="alert alert-warning alert-dismissable auto-fade">
								<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Some data that you have inserted is invalid..</div>';
									
							}
							else if($success == 1)
							{
								echo'<div class="alert alert-warning alert-dismissable auto-fade">
								<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>This anime has already added before..</div>';
							}
							else if($success == 2)
							{
								echo'<div class="alert alert-warning alert-dismissable auto-fade">
								<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Oops.. there is something wrong with our database, try again later..</div>';
									
							}
							else if($success == 3)
							{
								echo'<div class="alert alert-success alert-dismissable auto-fade">
								<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Data successfully recorded..</div>';

							}
							unset($_POST["ssubmit"]);
							unset($success);
						}
						?>
						<input id="ssubmit" type="submit" class="btn btn-default" name="ssubmit" style="float:right; margin-top:15px;" value="Submit">
						<?php
							
						?>
				</form>
			</div>
			<div class="tab-pane fade <?php 
				if(isset($_GET["action"])) 
				{ 
					if($_GET["action"] == "update")
					{
						echo "in active";
					}
				}
				?>" id="update">
				<form id="uform" name="uform" action="" class="form-horizontal" role="form" method="post" style="margin-top:25px">
					<fieldset>
						<div class="form-group">
							<label class="control-table col-sm-2" for="judulanimeupdate">Judul Anime</label>
							<div class="col-sm-10">
							<?php
								$qry = "SELECT * FROM $core->tablename ORDER BY judul ASC";
								$result = mysqli_query($core->conn, $qry);
								echo "<select id='judulanimeupdate' name='judulanimeupdate' class='form-control' required>";
								echo "<option value='' episode=''>Select One</option>";
								while($row = mysqli_fetch_array($result))
								{
									echo "<option title-id='" .$row["id"] ."' value='" .html_entity_decode($row['judul'])."' episode='" .$row['episode_total']."' episode-sekarang='" .$row['episode_sekarang']."'>".html_entity_decode($row['judul'])."</option>\n";
								}
								echo "</select>";
								//mysqli_free_result($result);
							?>
							<a style="display:none; float:right;" id="judulupdateedit" href="#">Edit</a>
							<input id="jdlhid" name="jdlhid" type="hidden" value=" ">
							</div>
							<script>
							$(document).ready(function(){
								$("#judulanimeupdate").change(function(){
									if($("#judulanimeupdate option:selected").val() != ""){
									var getep = $("#judulanimeupdate option:selected").attr("episode");
									var getepskrng = $("#judulanimeupdate option:selected").attr("episode-sekarang");
									var getid = $("#judulanimeupdate option:selected").attr("title-id");
									$("#episodetotalupdate").attr("value", getep);
									$("#episodesekarangupdate").attr("value", getepskrng);
									$("#jdlhid").attr("value", getid);
									$("#judulupdateedit").css("display", "block");
									//$("#orderoption option:selected").attr("selected");
									}
									else if($("#judulanimeupdate option:selected").val() == ""){
										$("#judulupdateedit").css("display", "none");
										$("#episodetotalupdate").attr("value", "");
										$("#episodesekarangupdate").attr("value", "");
										$("#jdlhid").attr("value", "");
									}
								})

								$("#judulupdateedit").click(function(event){
									var getjdl = $("#judulanimeupdate option:selected").val();
									$("#judulanimeupdate").replaceWith('<input class="form-control" type="text" name="judulanimeupdate" id="judulanimeupdate" value="' + getjdl +'">');
									//$("#judulupdateedit").removeAttr("readonly");
									$(this).remove();
									event.preventDefault();
								});
							});
							</script>
						</div>
						<div class="form-group">
							<label class="control-table col-sm-2">Episode Total</label>
							<div class="col-sm-10">
							<input type="number" name="episodetotalupdate" id="episodetotalupdate" placeholder="Episode Total" class="disabled form-control" size="24" readonly min="0">
							<a id="episodetotalupdateedit" style="float:right;" href="#">Edit</a>
							<script>
								$("#episodetotalupdateedit").click(function(event){
									$("#episodetotalupdate").removeAttr("readonly");
									$(this).remove();
									event.preventDefault();
								});

							</script>
							</div>
						</div>
						<div class="form-group">
							<label class="control-table col-sm-2">Episode Sekarang</label>
							<div class="col-sm-10">
							<input type="number" name="episodesekarangupdate" id="episodesekarangupdate" placeholder="Episode yang sedang ditonton" class="form-control" size="24" required min="0">
							</div>
						</div>
						<!--<div class="form-group">
							<label class="control-table col-sm-2">Status</label>
							<div class="col-sm-10">
							<select name="statusanimeupdate" required>
								<option value=""> </option>
								<option value="Done">Done</option>
								<option value="Not yet">Not yet</option>
							</select>
							</div>
						</div>-->
						<?php
						if(isset($_POST["usubmit"]))
						{
							if($success == 0)
							{
								echo '<div class="alert alert-warning alert-dismissable auto-fade">
								<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Some data that you have inserted is invalid..</div>';
									
							}
							else if($success == 1)
							{
								echo'<div class="alert alert-warning alert-dismissable auto-fade">
								<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Oops.. there is something wrong with our database, try again later..</div>';
									
							}
							else if($success == 2)
							{
								echo'<div class="alert alert-success alert-dismissable auto-fade">
								<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Data successfully updated</div>';

							}
							unset($_POST["usubmit"]);
							unset($success);
						}
						?>
						<input type="submit" class="btn btn-default" name="usubmit" value="Update" style="float:right; margin-top:15px;">
					</fieldset>
						<?php
							
						?>
				</form>
			</div>
			<div class="tab-pane fade <?php 
				if(isset($_GET["action"])) 
				{ 
					if($_GET["action"] == "delete")
					{
						echo "in active";
					}
				}
				?>" id="delete">
				<form id="dform" name="dform" action="" class="form-horizontal" role="form" method="post" style="margin-top:25px">
					<fieldset>
						<div class="form-group">
							<label class="control-table col-sm-2" for="judulanimedelete">Judul Anime</label>
							<div class="col-sm-10">
							<?php
								$qry = "SELECT * FROM $core->tablename ORDER BY judul ASC";
								$result = mysqli_query($core->conn, $qry);
								echo "<select id='judulanimedelete' name='judulanimedelete' class='form-control' required>";
								echo "<option value='' episode=''>Select One</option>";
								while($row = mysqli_fetch_array($result))
								{
									echo "<option value='" .html_entity_decode($row['judul'])."' episode='" .$row['episode_total']."'>".html_entity_decode($row['judul'])."</option>\n";
								}
								echo "</select>";
								//mysqli_free_result($result);
							?>
							</div>
							<!--<script>
								$(document).ready(function(){
								$("#judulanimedelete").change(function(){
									if($("#judulanimedelete option:selected") != ""){
									
									
									var getep = $("#judulanimedelete option:selected").attr("episode");
									$("#episodetotalupdate").attr("value", getep);
									//$("#orderoption option:selected").attr("selected");
									}
								})
							});
							</script>-->
						</div>
						<!--<input type="submit" class="btn btn-danger" name="dsubmit" value="Delete" style="float:right; margin-top:15px;">-->
						<script>
							function titleAni()
							{
								if($("#judulanimedelete option:selected").val() != ""){
									$("#dmodal").modal({backdrop: "static"});
									var content = $(".modal-body-content");
									var txt = $(".modal-body-content").text("Are you sure want to delete anime <b>" + $("#judulanimedelete option:selected").val() + "</b> from anime list?");
									content.innerHTML = txt;
								}
							}
							$("#judulanimedelete").change(function(){
								if($("#judulanimedelete option:selected").val() != "")
								{
									$(".btn-delete").removeClass("disabled");
								}
								else
								{
									$(".btn-delete").addClass("disabled");
								}
							});
						</script>
						<?php
						if(isset($_POST["dsubmit"]))
						{
							if($success == 0)
							{
								echo'<div class="alert alert-warning alert-dismissable auto-fade">
									<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Oops.. there is something wrong with our database, try again later..</div>';
										
							}
							else if($success == 1)
							{
								echo'<div class="alert alert-success alert-dismissable auto-fade">
								<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Data successfully deleted</div>';

							}
							unset($_POST["dsubmit"]);
							unset($success);
						}
						?>
						<button style="float:right; margin-top:15px;" onclick="titleAni()" class="btn btn-danger btn-delete disabled" type="button" data-toggle="modal">Delete</button>
						<div id="dmodal" class="modal fade" role="dialog">
							<div class="modal-dialog">
								<div class="modal-content">
									<div class="modal-header">
										<button type="button" class="close" data-dismiss="modal">&times;</button>
										<h4 class="modal-title modal-alert">Alert!</h4>
									</div>
									<div class="modal-body">
										<p class="modal-body-content"></p>
									</div>
									<div class="modal-footer">
										<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
										<input type="submit" name="dsubmit" class="btn btn-danger" value="Delete">
									</div>
								</div>
							</div>
						</div>
					</fieldset>
				</form>
			</div>
		</div>
		<a style="display:block; margin-top:10px; margin-bottom:20px;" class="btn btn-default" data-toggle="collapse" href="#div-table-1">Show/Hide Table</a>
		<label>Order by: </label>
		<select name="orderoption" id="orderoption">
			<option value="">Select One</option>
			<?php
			if(isset($_GET["orderby"]))
			{
				if($_GET["orderby"] == "judul"){
					echo "<option value='judul' selected>Name</option>";
					echo "<option value='status'>Status</option>";
					echo "<option value='last_updated'>Last Updated</option>";
				}
				else if($_GET["orderby"] == "status"){
					echo "<option value='judul'>Name</option>";
					echo "<option value='status' selected>Status</option>";
					echo "<option value='last_updated'>Last Updated</option>";
				}
				else if($_GET["orderby"] == "last_updated"){
					echo "<option value='judul'>Name</option>";
					echo "<option value='status'>Status</option>";
					echo "<option value='last_updated' selected>Last Updated</option>";
				}
				else{
					echo "<option value='judul'>Name</option>";
					echo "<option value='status'>Status</option>";
					echo "<option value='last_updated'>Last Updated</option>";
				}
			}
			else{
				echo "<option value='judul'>Name</option>";
				echo "<option value='status'>Status</option>";
				echo "<option value='last_updated'>Last Updated</option>";
			}
			?>
		</select>
		<a class="btn btn-default" href="/anidb.php">Reset</a>
		<br/>
		<span>If the data not updated, please <a href="/anidb.php">Refresh</a></span>
		<?php
			if(isset($_GET["orderby"]))
			{
				if($_GET["orderby"] == "judul"){
					$qry = "SELECT * FROM $core->tablename ORDER BY judul";
				}
				else if($_GET["orderby"] == "status"){
					$qry = "SELECT * FROM $core->tablename ORDER BY status DESC, judul ASC";
				}
				else if($_GET["orderby"] == "last_updated"){
					$qry = "SELECT id, judul, episode_total, episode_sekarang, status, last_updated FROM $core->tablename ORDER BY last_updated DESC";
				}
				else{
					$qry = "SELECT * FROM $core->tablename ORDER BY judul";
				}
			}
			else{
				$qry = "SELECT * FROM $core->tablename ORDER BY judul";
			}
			$result = mysqli_query($core->conn, $qry);
			echo '<div class="collapse in" id="div-table-1">';
			echo "<table id='table-1' class='table table-striped table-bordered'>";
			echo "<thead>";
			echo "<tr>
					<th>No.</th>
					<th>Judul</th>
					<th>Episode Total</th>
					<th>Episode Terakhir Yang <br/>Sedang di Tonton</th>
					<th>Status</th>
					<th>Last Updated Data</th>
				  </tr>";
			echo "</thead>\n";
			echo "<tbody id='table-2'>\n";
			if(mysqli_num_rows($result) <= 0)
			{
				echo "<tr>Error: There is nothing in database</tr>";
				echo "</table>";
				return true;
			}
			$count = 0;
			while($row = mysqli_fetch_array($result))
			{
				$count++;
				echo "<tr><td>";
				echo $count;
				echo "</td>";
				echo "<td title='" . htmlspecialchars_decode($row['judul']) ."'>";
				echo htmlspecialchars_decode($row['judul']);
				echo "</td>";
				echo "<td>";
				echo $row["episode_total"];
				echo "</td>";
				echo "<td>";
				echo $row["episode_sekarang"];
				echo "</td>";
				echo "<td id='status'>";
				echo $row["status"];
				echo "</td>";
				echo "<td>";
				echo $row["last_updated"];
				echo "</td></tr>\n";
				
			}
			echo "</tbody>";
			echo "</table>";
			echo "<ul class='pagination pagination-lg pager' id='myPager'></ul>";
			echo "</div>";
		?>
		<script>
			var ths = document.getElementsByTagName('th');
			var tbl = document.getElementById("table-1");
			var tds = tbl.querySelectorAll('td#status');
			for (var i=0; i<tds.length; i++) {
				//var st = tds.getElementById("status");
				if(tds[i].innerHTML.match("Done")) {
					tds[i].style.backgroundColor = "#388E3C";
				}
				else if(tds[i].innerHTML.match("Not yet"))
				{
					tds[i].style.backgroundColor = "#D32F2F";
				}
				else
				{
					tds[i].style.backgroundColor = "#F57C00";
				}
			}
			
			function isInteger(n) {
				return /^[0-9]+$/.test(n);
			}
			
			$(document).ready(function(){
				//$("#orderoption option:selected").attr("selected");
				$("#orderoption").change(function(){
					if($("#orderoption option:selected").val() != ""){
						var loc = window.location.href;
						/*if(!GetURLParameter('action'))
						{
							window.location.href = "anidb.php?orderby=" + $("#orderoption option:selected").val();
						}
						else{
							window.location.href = "anidb.php?orderby=" + $("#orderoption option:selected").val()+ "&action=" + GetURLParameter("action");
						}*/
						changeURLParameter("orderby", $("#orderoption option:selected").val());
						//addURLParameter("aaa", "aaa");
					}
				});
				$(".auto-fade").fadeTo(10000, 0).slideUp(500, function(){
					$(this).remove(); 
				});
				
				$("#episodetotal").change(function(){
					var eptotal = $("#episodetotal").val();
					if(!isInteger(eptotal) && eptotal != ""){
						$("#episodetotal").attr("data-content","It's not numeric");
						$("#episodetotal").popover("show");
						//alert("it's numeric");
					}
					else
					{
						$("#episodetotal").popover("destroy");
					}
				});
				
				$("#episodesekarang").change(function(){
					var epsekarang = $("#episodesekarang").val();
					if(!isInteger(epsekarang) && epsekarang != ""){
						$("#episodesekarang").attr("data-content","It's not numeric");
						$("#episodesekarang").popover("show");
						//alert("it's numeric");
					}
					else
					{
						$("#episodesekarang").popover("destroy");
					}
				});
				
			});

		</script>
		<hr/>
		</div>
	</body>
</html>
<?php
mysqli_close($core->conn);
?>