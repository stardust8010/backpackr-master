<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Api extends CI_Controller {

	function __construct(){
		parent::__construct();

		// 모델 불러오기
		$this->load->model('Member');
		// 라이브러리 불러오기
		$this->load->library('session');
	}

	public function index()
	{
		
	}


	public function member_regist()
	{
		$aValidation = array();

		// _POST 
		$data['vcEmail'] =  $this->input->post('email');		//이메일
		$data['vcPassword'] = $this->input->post('password');
		$data['vcPasswordRe'] = $this->input->post('password_confirm');
		$data['vcName'] = $this->input->post('username');
		$data['vcHp'] = $this->input->post('cell_phone');
		$data['emEvenAlarm'] = $this->input->post('marketing'); // Y OR N
		$data['rcmd_code'] = $this->input->post('rcmd_code');	// 추천인코드
		
		// 유효성 체크
		if(empty($data['vcEmail'])) $aValidation['email'] = 'Email Empty';
		if(empty($data['vcPassword'])) $aValidation['password'] = 'Password Empty';
		if(empty($data['vcPasswordRe'])) $aValidation['password_confirm'] = 'PasswordRe Empty';
		if($data['vcPassword'] != $data['vcPasswordRe']) 	$aValidation[] = 'Not Match Password';
		if(empty($data['vcName'])) $aValidation['username'] = 'Name Empty';
		if(empty($data['vcHp'])) $aValidation['cell_phone'] = 'Hp Empty';
		
		if(count($aValidation)>=1){
			$aRtn['result'] = false;
			$aRtn['error_type'] = 'validation';
			$aRtn['message'] = $aValidation;
			echo json_encode($aRtn);
			exit;
		}

		$aRtn = $this->Member->regist($data);
		if($aRtn['result'] === true){
			$this->session->set_userdata('nMeberSeqNo', $aRtn['nMeberSeqNo']);
		}
		echo json_encode($aRtn);
	}

	public function member_modify()
	{

		$aValidation = array();

		// _POST 
		$data['vcPassword'] = $this->input->post('password');
		$data['vcPasswordRe'] = $this->input->post('password_confirm');
		$data['vcName'] = $this->input->post('username');
		$data['vcHp'] = $this->input->post('cell_phone');
		$data['emEvenAlarm'] = $this->input->post('marketing'); // Y OR N
		$data['rcmd_code'] = $this->input->post('rcmd_code');	// 추천인코드


		// 세션처리 (회원 Seq)
		$data['nSeqNo'] = $this->session->userdata('nMeberSeqNo');;

		// 유효성 체크
		if(empty($data['vcPassword'])) $aValidation['password'] = 'Password Empty';
		if(empty($data['vcPasswordRe'])) $aValidation['password_confirm'] = 'PasswordRe Empty';
		if($data['vcPassword'] != $data['vcPasswordRe']) 	$aValidation[] = 'Not Match Password';
		if(empty($data['vcName'])) $aValidation['username'] = 'Name Empty';
		if(empty($data['vcHp'])) $aValidation['cell_phone'] = 'Hp Empty';
		
		if(count($aValidation)>=1){
			$aRtn['result'] = false;
			$aRtn['error_type'] = 'validation';
			$aRtn['message'] = $aValidation;
			echo json_encode($aRtn);
			exit;
		}

		$aRtn = $this->Member->modify($data);
		echo json_encode($aRtn);
	}

	public function member_delete()
	{

		// 세션처리 (회원 Seq)
		$data['nSeqNo'] = $this->session->userdata('nMeberSeqNo');

		if(empty($data['nSeqNo'])){
			$aRtn['result'] = false;
			echo json_encode($aRtn);
			exit;
		}

		$aRtn = $this->Member->delete($data);
		echo json_encode($aRtn);
	}

	public function member_info()
	{

		// 세션처리 (회원 Seq)
		$data['nSeqNo'] = $this->session->userdata('nMeberSeqNo');

		$aRtn = $this->Member->member_inquiry($data);
		
		echo "<pre>";
		print_r($aRtn['list']);
		echo "</pre>";
	}

	public function member_list()
	{
		$nTotalCount = $this->Member->member_count();
		$limit = 10;
		$total_page = ($nTotalCount/$limit);
		$page = 1;
		$start = $page>1 ? ($page-1)*$limit : 0;
		$data['option'] = array('total_page'=>ceil($nTotalCount/$limit),'total_rows'=>$nTotalCount,'page'=>$page,'limit'=>$limit);

		$data['start'] = $start;
		$data['limit'] = $limit;
		$aRtn = $this->Member->member_inquiry($data);
		
		echo "<pre>";
		print_r($aRtn['list']);
		echo "</pre>";
	}
	
	// 회원 테이블 생성
	public function create_member_table()
	{
		$this->Member->create_member_table();
	}
	

}
