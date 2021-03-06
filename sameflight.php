<?php
session_start();
$form_error=null;
$id=$_SESSION['google_data']['id'];
$email=$_SESSION['google_data']['email'];
if(!isset($_SESSION['google_data'])):header("Location:index.php");endif;
include_once("google_includes/functions.php");
$gUser=new Users();
if($gUser->has_phoneno($_SESSION['google_data']['id'])); else{header("Location:inputphoneno.php");}
$my_traveldate=$_SESSION['google_data']['traveldate'];
$my_traveltime=$_SESSION['google_data']['traveltime'];
$my_travelfrom=$_SESSION['google_data']['travelfrom'];
$my_travelfrom=travel_no_words($my_travelfrom);
$my_travelto=$_SESSION['google_data']['travelto'];
$my_travelto=travel_no_words($my_travelto);
$my_flightno=$_SESSION['google_data']['flightno'];
$all_travellers=$gUser->all_travellers_ordertime();//returns assoc array
?>
<?php
global $title;
$title="VIT Cab Share - Same flight/train";
require_once('includes/head.php');
?>
<link rel="stylesheet" type="text/css" href="css/common.css">
</head>
<body>
	<?php 
	global $active_nav;
	$active_nav="sameflight";
	require_once('includes/navigation.php');
	?>
	<main>
		<br><br>
		<?php 
		if (!$my_travelfrom or !$my_travelto)
		{
			?>
			<div class="no-travel-intro thoda-side-mobile">You need to <a href="editdetails.php"><span class="chip"><i class="fa fa-pencil" aria-hidden="true"></i> add</span></a> your travel details</div>
			<?php
		}
		elseif (!has_presence($my_flightno))
		{
			?>
			<div class="no-travel-intro thoda-side-mobile">You need to <a href="editdetails.php"><span class="chip"><i class="fa fa-pencil" aria-hidden="true"></i> add</span></a> your flight/Train no.</div>
			<?php
		}
		else
		{
			?>
			<div class="hide-on-small-only">
				<h6 class="samedate-intro thoda-side-mobile" id="info1">People travelling on <span class="chip"><?php echo $my_traveldate;?></span> from <span  class="chip"><?php echo $my_travelfrom;?></span> to <span  class="chip"><?php echo $my_travelto;?></span> on flight/train <span  class="chip"><?php echo $my_flightno;?></span> are:</h6>
			</div>
			<div class="hide-on-med-and-up">
				<h6 class="samedate-intro thoda-side-mobile" id="info2">People travelling on <span class="text-light"><?php echo $my_traveldate;?></span> from <span  class="text-light"><?php echo $my_travelfrom;?></span> to <span  class="text-light"><?php echo $my_travelto;?></span> on flight/train <span  class="chip"><?php echo $my_flightno;?></span> are:</h6>
			</div>
			<h5 id="info0"></h5>
			<?php
		}
		?>
		<br><br>
		<div class="container">
			<div class="all-passengers">
				<?php
				$no_passengers=0;
				if($my_travelfrom and $my_travelto and has_presence($my_flightno))
				{
					while($row=mysqli_fetch_assoc($all_travellers))
					{
						$oauth_uid=$row['oauth_uid'];
						$lnamee=$row_from["lname"];
						if(!$row_from["lnamevisible"])
							$lnamee="";
						$name=$row["fname"]." ".$lnamee;
						$email=$row["email"];
						$picture=$row["picture"];
						$travelfrom=$row["travelfrom"];
						$travelto=$row["travelto"];
						$traveldate=$row["traveldate"];
						$traveltime=$row["traveltime"];
						$flightno=$row["flightno"];
						$emailvisible=$row["emailvisible"];
						$phoneno=$row["phoneno"];
						$phonenovisible=$row["phonenovisible"];
						$from_uid=$id;
						$to_uid=$oauth_uid;
						$request_already_send=$gUser->check_request($from_uid,$to_uid);
						$travelfrom=travel_no_words($travelfrom);
						$travelto=travel_no_words($travelto);
						if($travelfrom and $travelto and $travelfrom==$my_travelfrom and $travelto==$my_travelto and $traveldate==$my_traveldate and $my_flightno==$flightno)
						{
							$no_passengers++;
							?>
							<section class="passenger card-panel hoverable" id="<?php echo "sec".$oauth_uid;?>">
								<div class="row">

									<div class="timediv"> <i class="fa fa-clock-o" aria-hidden="true"></i>
										<span><?php echo $traveltime;?></span>
									</div>
									<div class="imgdiv col s3 center-align">
										<img src="<?php echo $picture;?>">
									</div>
									<div class="detailsdiv col s9">
										<h6 class="fontsize14rem"><b><?php echo ucwords($name);?></b></h6>
										<?php 
										if(($emailvisible=="0"||$emailvisible==0 ) && ($phonenovisible=="0"||$phonenovisible==0))
										{
											?>
											<p>I dont want to provide my email or phone number. You can only send a request to me.</p>
											<?php
										}
										else
										{
											?>
											<p>You can contact me at <?php if($emailvisible=="1"||$emailvisible==1) echo $email."  ";?><?php if(($emailvisible=="1"||$emailvisible==1 ) && ($phonenovisible=="1"||$phonenovisible==1)) echo " or ";?><?php if($phonenovisible=="1"||$phonenovisible==1) echo $phoneno;?>.</p>
											<?php
										}
										?>
										<a class="requestbutton waves-effect waves-light btn right <?php if($request_already_send){echo 'disabled';} else{echo 'modal-trigger';}?>" name="<?php echo $name?>" id="<?php echo $oauth_uid;?>" <?php if(!$request_already_send) echo "href='#modal1'"?> ><i class="fa fa-envelope-o" aria-hidden="true"></i> <?php if($request_already_send){echo 'send';} else{echo 'send request';}?></a>
									</div>
								</div>
							</section>
							<?php
						}
					}
				}
				?>

				<!-- Modal Structure -->
				<div id="modal1" class="modal">
					<div class="modal-content">
						<h5>Send a request to <span id="receive_name_span"></span> for sharing cab?</h5>
						<p><i>Note</i> : The person you send the request, will be able to see your email and phone number to contact you back.</p>
						
					</div>
					<div class="modal-footer">
						<form action="sendrequest.php" method="POST">
							<input type="text" class="hide" id="redirect_page" name="redirect_page">
							<input type="text" class="hide" id="sendrequest_receive" name="sendrequest_receive">
							<input type="submit" name="submitrequest" value="Yes" class="yesoption optionbutton btn-flat modal-action modal-close waves-effect waves-green">
						</form>
						<a class="nooption optionbutton modal-action modal-close waves-effect waves-green btn-flat">No</a>
					</div>
				</div>
			</div>
		</div>

	</main>
	<?php 
	include_once('includes/scripts.php');
	?>
	<?php
	if($no_passengers==0)
	{
		?>
		<script type="text/javascript">
			$("#info1").addClass("hide");
			$("#info2").addClass("hide");
			$("#info0").text("No one found travelling with you.");
		</script>
		<?php
	}
	?>
	<script type="text/javascript">
		$(".requestbutton").click(function(){
			var idd=this.id;
			$("#sendrequest_receive").val(idd);
			$("#receive_name_span").text(this.name);
			$("#redirect_page").val("sameflight.php#sec"+idd);
		});
	</script>
	<?php
	include_once('includes/footer.php');
	?>
	<?php
	if(isset($_SESSION['sendrequest_success']) and $_SESSION['sendrequest_success'])
	{
		$_SESSION['sendrequest_success']=null;
		?>
		<script>
			alert("Successfully send request!");
		</script>
		<?php

	}
	?>