
<!DOCTYPE html>

<html>
<link href="<?= base_url()?>css/test.css" type="text/css" rel="stylesheet" />
	<head>
	<script src="http://code.jquery.com/jquery-latest.js"></script>
	<script src="<?= base_url() ?>/js/jquery.timers.js"></script>
	<script>

		var otherUser = "<?= $otherUser->login ?>";
		var user = "<?= $user->login ?>";
		var status = "<?= $status ?>";
		
		$(function(){
			$('body').everyTime(200,function(){
					if (status == 'waiting') {
						$.getJSON('<?= base_url() ?>arcade/checkInvitation',function(data, text, jqZHR){
								if (data && data.status=='rejected') {
									alert("Sorry, your invitation to play was declined!");
									window.location.href = '<?= base_url() ?>arcade/index';
								}
								if (data && data.status=='accepted') {
									status = 'playing';
									$('#status').html('Playing ' + otherUser);
								}
								
						});
					}
						
						var url = "<?= base_url() ?>board/getMsg";
						$.getJSON(url, function (data,text,jqXHR){
							if (data && (data.status=='success' || data.status== 'otherWon')) {
								var msg = data.message;
								if (msg.length > 0){
						    		var temp = '[id=pic';
						    		temp = temp.concat(msg.toString());
						    		temp = temp.concat(']');
									$(temp).attr('src', '<?= base_url() ?>images/Box_Yellow.png');
									var remains = msg%6;
									var column = (msg - remains) / 6;
									if ( remains != 0){
										column = column + 1;
									}
									var temp2 = '[id=inner';
									temp2 = temp2.concat(column);
									temp2 = temp2.concat(']');
									var temp3 = $(temp2).attr('data-col');
									$(temp2).attr('data-col', temp3-1);
									if ( data.status == 'otherWon' ){
										var lock = $('[id=outerDiv]').attr('data-lock');
										if ( lock == 1 ){
											alert('The other player has won!');
											$('[id=outerDiv]').attr('data-lock', 2);
											}}
									else{
										$('[id=outerDiv]').attr('data-lock', 0);
									}
								};
							}
						});
			});

			$('[id=outerDiv]').click(function(){
				var arguments = $('form').serialize();
				var lock = $('[id=outerDiv]').attr('data-lock');
				if ( lock == 0 ){
					if( $('[name=msg]').val().length > 0){
						$('[id=outerDiv]').attr('data-lock', 1);
						var url = "<?= base_url() ?>board/postMsg";
						$.post(url,arguments, function (data,textStatus,jqXHR){
							if (data == '"youWon"'){
								alert('you won!!!');
								var url = "<?= base_url() ?>board/gameSetWin";
								$.post(url, user);
							}
						});
					return false;
					}}
				});	
		});
	
	</script>
	</head> 
<body>  
	<h1>Game Area</h1>

	<div>
	Hello <?= $user->fullName() ?>  <?= anchor('account/logout','(Logout)') ?>
	<br> 
	<?= anchor('account/backToArcade', 'BackToArcade(Use only after game is finished)')?>
	</div>
	
	<div>
	Your play is colored in Blue, your opponent is in Yellow !
	</div>
	
	<div id='status'> 
	<?php 
		if ($status == "playing")
			echo "Playing " . $otherUser->login;
		else
			echo "Wating on " . $otherUser->login;
	?>
	</div>
	
	<?php 
	
	echo form_open();
	echo form_hidden('msg');
	echo form_close();
	
	
	
	$this->load->helper('url');
	
	echo " <div id = 'outerDiv' data-lock = ";
	if ($status == "playing")
		echo 0;
	else
		echo 1; 
	echo ">";
	
	for( $i = 1; $i <=7; $i++){
		echo "<div id = 'inner";
		echo $i;
		echo "' style='height: 315px;  width: 50px;' onclick = updateCol";
		echo $i;
		echo "(this) data-col = ";
		echo $i*6;
		echo ">";
		for ( $m = 1; $m <= 6; $m++){
			echo " <img src='";
			echo base_url();
			echo "images/new.png' id='pic";
			echo ($i-1)*6 + $m;
			echo "'/>";
		}
		echo "</div>";
	}
	
	echo "</div>";
	
	
	
	echo "<script type='text/javascript'>";
	
	for ($u = 1; $u <=7; $u++){
		echo "function updateCol";
		echo $u;
		echo " (obj){";
		echo "var outer = document.getElementById('outerDiv');";
		echo "var lock = outer.getAttribute('data-lock');";
		echo "if( lock == 0){"; 
		echo "var cur = obj.getAttribute('data-col');";
		echo "if( cur >";
		echo ($u - 1)* 6;
		echo "){
    		var temp = 'pic';
    		var temp2 = cur;
			   temp = temp.concat(temp2);
			   var gp = document.getElementById(temp);
    		   gp.src = '";
		echo base_url();
		echo "images/Box_Blue.png';";
		echo "$('[name=msg]').val(temp2);";
		echo "obj.setAttribute('data-col', temp2-1);";
		echo "} else{";
		echo "$('[name=msg]').val('');";
		echo "}";
		echo "}}";	
	}
	
	
	echo "</script>";
	
	
	
	
	
	
	
	
	?>

    
    
    
    	
    	
    	
    	
    
      
    
    
    
    
	
</body>

</html>

