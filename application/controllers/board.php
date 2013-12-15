<?php

class Board extends CI_Controller {
     
    function __construct() {
    		// Call the Controller constructor
	    	parent::__construct();
	    	session_start();
    } 
          
    public function _remap($method, $params = array()) {
	    	// enforce access control to protected functions	
    		
    		if (!isset($_SESSION['user']))
   			redirect('account/loginForm', 'refresh'); //Then we redirect to the index page again
 	    	
	    	return call_user_func_array(array($this, $method), $params);
    }
    
    
    function index() {
		$user = $_SESSION['user'];
    		    	
	    	$this->load->model('user_model');
	    	$this->load->model('invite_model');
	    	$this->load->model('match_model');
	    	
	    	$user = $this->user_model->get($user->login);

	    	$invite = $this->invite_model->get($user->invite_id);
	    	
	    	if ($user->user_status_id == User::WAITING) {
	    		$invite = $this->invite_model->get($user->invite_id);
	    		$otherUser = $this->user_model->getFromId($invite->user2_id);
	    	}
	    	else if ($user->user_status_id == User::PLAYING) {
	    		$match = $this->match_model->get($user->match_id);
	    		if ($match->user1_id == $user->id)
	    			$otherUser = $this->user_model->getFromId($match->user2_id);
	    		else
	    			$otherUser = $this->user_model->getFromId($match->user1_id);
	    	}
	    	
	    	$data['user']=$user;
	    	$data['otherUser']=$otherUser;
	    	
	    	switch($user->user_status_id) {
	    		case User::PLAYING:	
	    			$data['status'] = 'playing';
	    			break;
	    		case User::WAITING:
	    			$data['status'] = 'waiting';
	    			break;
	    	}
	    	
		$this->load->view('match/board',$data);
    }

 	function postMsg() {
 		$this->load->library('form_validation');
 		$this->form_validation->set_rules('msg', 'Message', 'required');
 		
 		if ($this->form_validation->run() == TRUE) {
 			$this->load->model('user_model');
 			$this->load->model('match_model');

 			$user = $_SESSION['user'];
 			 
 			$user = $this->user_model->get($user->login);
 			if ($user->user_status_id != User::PLAYING) {	
				$errormsg="Not in PLAYING state";
 				goto error;
 			}
 			
 			$match = $this->match_model->get($user->match_id);			
 			
 			$msg = $this->input->post('msg');
 			
 			if ($match->user1_id == $user->id)  {
 				
 				$blob = $match->board_state;
 				$blob .= '1';
 				if ( strlen($msg) == 1){
 					$blob .= '0';
 				}
 				$blob .= $msg;
 				$blob .= '.';
 				$this->match_model->updateBlob($match->id, $blob);
 				
 				$msg = $match->u1_msg == ''? $msg :  $match->u1_msg . "\n" . $msg;
 				$this->match_model->updateMsgU1($match->id, $msg);
 			}
 			else {
 				$blob = $match->board_state;
 				$blob .= '2';
 				if ( strlen($msg) == 1){
 					$blob .= '0';
 				}
 				$blob .= $msg;
 				$blob .= '.';
 				$this->match_model->updateBlob($match->id, $blob);
 				
 				$msg = $match->u2_msg == ''? $msg :  $match->u2_msg . "\n" . $msg;
 				$this->match_model->updateMsgU2($match->id, $msg);
 			}

 			$match = $this->match_model->get($user->match_id);			
 			$BLOB = substr($match->board_state, 0, count($match->board_state)-2);
 			$a= explode('.',$BLOB);
 				
 			$newarray1 =array();
 			$newarray2 =array();
 			$winner = array();
 			if( count($a) > 3){
 				foreach($a as $c){
 					if ($c[0] ==1){
 						$firstdigit = $c[1];
 						$seconddigit =$c[2];
 						$index = 10* $firstdigit + $seconddigit;
 						array_push($newarray1,$index);
 					}else if($c[0]==2){
 						$firstdigit = $c[1];
 						$seconddigit =$c[2];
 						$index = 10* $firstdigit + $seconddigit;
 						array_push($newarray2,$index);
 					};
 				};
 				if ($match->user1_id == $user->id) {
 					$all = $newarray1;}
 				else {
 					$all = $newarray2;};
 				$temp = $all;
 							
 				foreach ($temp as $num){
 					if (in_array($num-1,$temp)){
 						if (in_array($num-2,$temp)){
 							if (in_array($num-3,$temp)){
 								array_push($winner,$num);
 								array_push($winner,$num-1);
 								array_push($winner,$num-2);
 								array_push($winner,$num-3);}}}}	;
 				foreach ($temp as $num){
 					if (in_array($num-6,$temp)){
 						if (in_array($num-12,$temp)){
 							if (in_array($num-18,$temp)){
 								array_push($winner,$num);
 								array_push($winner,$num-6);
 								array_push($winner,$num-12);
 								array_push($winner,$num-18);}}}};
 				foreach ($temp as $num){
 					if (in_array($num-5,$temp)){
 						if (in_array($num-10,$temp)){
 							if (in_array($num-15,$temp)){
 								array_push($winner,$num);
 								array_push($winner,$num-5);
 								array_push($winner,$num-10);
 								array_push($winner,$num-15);}}}};
 				foreach ($temp as $num){
 					if (in_array($num-7,$temp)){
 						if (in_array($num-14,$temp)){
 							if (in_array($num-21,$temp)){
 								array_push($winner,$num);
 								array_push($winner,$num-7);
 								array_push($winner,$num-14);
 								array_push($winner,$num-21);}}}};
 			};
 			
 			
 			
 			
 			
 			
 			
 			if ( count($winner) == 4 ){
 				echo json_encode('youWon');
 			}
 			else{
 				echo json_encode('success');
 			}
 			
 			return;
 			
 			
 			
 			 
 		}
		
 		$errormsg="Missing argument";
 		
		error:
			echo json_encode(array('status'=>'failure','message'=>$errormsg));
 	}
 
	function getMsg() {
 		$this->load->model('user_model');
 		$this->load->model('match_model');
 			
 		$user = $_SESSION['user'];
 		 
 		$user = $this->user_model->get($user->login);
 		if ($user->user_status_id != User::PLAYING) {	
 			$errormsg="Not in PLAYING state";
 			goto error;
 		}
 		// start transactional mode  
 		$this->db->trans_begin();

 		$match = $this->match_model->getExclusive($user->match_id);			
 		####################### try to detect winner
 		$BLOB = substr($match->board_state, 0, count($match->board_state)-2);
 		$a= explode('.',$BLOB);
 		
 		$newarray1 =array();
 		$newarray2 =array();
 		$winner = array();
 		if( count($a) > 3){
 			foreach($a as $c){
				if ($c[0] ==1){
					$firstdigit = $c[1];
					$seconddigit =$c[2];
					$index = 10* $firstdigit + $seconddigit;
					array_push($newarray1,$index);
				}else if($c[0]==2){
					$firstdigit = $c[1];
					$seconddigit =$c[2];
					$index = 10* $firstdigit + $seconddigit;
					array_push($newarray2,$index);
				};
			};
			if ($match->user1_id == $user->id) {
				$all = $newarray2;}
			else {
				$all = $newarray1;};
 			$temp = $all;
 		
 			foreach ($temp as $num){
				if (in_array($num-1,$temp)){
					if (in_array($num-2,$temp)){
						if (in_array($num-3,$temp)){
							array_push($winner,$num);
							array_push($winner,$num-1);
							array_push($winner,$num-2);
							array_push($winner,$num-3);}}}}	;
 			foreach ($temp as $num){
				if (in_array($num-6,$temp)){
					if (in_array($num-12,$temp)){
						if (in_array($num-18,$temp)){
							array_push($winner,$num);
							array_push($winner,$num-6);
							array_push($winner,$num-12);
							array_push($winner,$num-18);}}}};
		 	foreach ($temp as $num){
				if (in_array($num-5,$temp)){
					if (in_array($num-10,$temp)){
						if (in_array($num-15,$temp)){
							array_push($winner,$num);
							array_push($winner,$num-5);
							array_push($winner,$num-10);
							array_push($winner,$num-15);}}}};
 		 	foreach ($temp as $num){
				if (in_array($num-7,$temp)){
					if (in_array($num-14,$temp)){
						if (in_array($num-21,$temp)){
							array_push($winner,$num);
							array_push($winner,$num-7);
							array_push($winner,$num-14);
							array_push($winner,$num-21);}}}};
 		};
 		#######################	
 		if ($match->user1_id == $user->id) {
			$msg = $match->u2_msg;
 			$this->match_model->updateMsgU2($match->id,"");	
 		}
 		else {
 			$msg = $match->u1_msg;
 			$this->match_model->updateMsgU1($match->id,"");
 		}

 		if ($this->db->trans_status() === FALSE) {
 			$errormsg = "Transaction error";
 			goto transactionerror;
 		}
 		
 		// if all went well commit changes
 		$this->db->trans_commit();
 		
 		if ( count($winner) == 4 ){
			echo json_encode(array('status'=>'otherWon','message'=>$msg, 'winner'=>$winner));
		}
		else{
				echo json_encode(array('status'=>'success','message'=>$msg ));
		}
		return;
		
		transactionerror:
		$this->db->trans_rollback();
		
		error:
		echo json_encode(array('status'=>'failure','message'=>$errormsg));
 	}

 	
 	function gameSetWin(){
 		$this->load->model('match_model');
 		$this->load->model('user_model');
 		
 	 	$user = $_SESSION['user'];
 		 
 		$user = $this->user_model->get($user->login);
 		if ($user->user_status_id != User::PLAYING) {	
 			$errormsg="Not in PLAYING state";
 			goto error;
 		}
 		
 		$match = $this->match_model->get($user->match_id);
 		
 		
 		
 		
 		if($user->id == $match->user1_id){
 			$this->match_model->updateStatus($user->match_id, Match::U1WON);
 		}
 		else{
 			$this->match_model->updateStatus($user->match_id, Match::U2WON);
 		}
 		
 		
 		
 		
 		
 		return;
 		
 		
 		
 		
 		error:
 			echo json_encode(array('status'=>'failure','message'=>$errormsg));
 	}
 	
 	
 	
 }

