<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Member extends CI_Model {
	function __construct(){
		parent::__construct();
		$this->member_table = "member"; // 회원테이블
	}

	// 이메일 존재하는지 체크 
	// (삭제여부 상관없이 조회)
	function exist_check($data){
		$vcEmail = isset($data['vcEmail']) ? $data['vcEmail'] : '';
		$aResult = $this->db->where('vcEmail',$vcEmail)->get($this->member_table)->result_array();
		$aRtn['total'] = count($aResult);
		return $aRtn;
	}

	// 회원 정보 등록
	function regist($data){
		$aRtn = array();
		$aValidation = array();

		$aRtn['result'] = false;
		$vcEmail = isset($data['vcEmail']) ? $data['vcEmail'] : '';
		$vcPassword = isset($data['vcPassword']) ? $data['vcPassword'] : '';
		$vcName = isset($data['vcName']) ? $data['vcName'] : '';
		$vcHp = isset($data['vcHp']) ? $data['vcHp'] : '';
		
		$emEvenAlarm = isset($data['emEvenAlarm']) ? $data['emEvenAlarm'] : 'N';
		$vcRecommendCode = isset($data['vcRecommendCode']) ? $data['vcRecommendCode'] : '';

		// 유효성 체크
		if(empty($data['vcEmail'])) $aValidation['email'] = 'Email Empty';
		if(empty($data['vcPassword'])) $aValidation['password'] = 'Password Empty';
		if(empty($data['vcPasswordRe'])) $aValidation['password_confirm'] = 'PasswordRe Empty';
		if($data['vcPassword'] != $data['vcPasswordRe']) 	$aValidation[] = 'Not Match Password';
		if(empty($data['vcName'])) $aValidation['username'] = 'Name Empty';
		if(empty($data['vcHp'])) $aValidation['cell_phone'] = 'Hp Empty';
		if(count($aValidation)>=1){
			json_encode($aValidation);
			exit;
		}

		$exist = $this->exist_check($data);
		if($exist['total'] >= 1){
			$aRtn['error_type'] = 'email_exist';
			return $aRtn;
		};


		// DB 입력
		$this->db->set('vcEmail', $vcEmail);
		$this->db->set('vcPassword', md5($vcPassword));
		$this->db->set('vcName', $vcName);
		$this->db->set('vcHp', $vcHp);
		$this->db->set('dtRegDate','NOW()',false);
		$this->db->set('dtUseAgree','NOW()',false);
		$this->db->set('dtPersonAgree','NOW()',false);
		$this->db->set('emEvenAlarm', $emEvenAlarm);
		if($data['emEvenAlarm'] == 'Y') $this->db->set('dtEvenAlarm','NOW()',false);		// 쿠폰/이벤트 알림 받기 (선택)
		if(!empty($vcRecommendCode)) $this->db->set('vcRecommendCode',$vcRecommendCode);		// 추천인코드


		$aRtn['result'] = $this->db->insert($this->member_table);
		if($aRtn['result'] === true){
			$aRtn['nMeberSeqNo'] = $this->db->insert_id();
		}else{
			$aRtn['error_type'] = 'insert_error';
		}

		return $aRtn;
	}

	// 회원 정보 수정
	function modify($data){
		$aRtn = array();
		$aValidation = array();

		$aRtn['result'] = false;

		$nSeqNo = isset($data['nSeqNo']) ? $data['nSeqNo'] : '';
		$vcPassword = isset($data['vcPassword']) ? $data['vcPassword'] : '';
		$vcName = isset($data['vcName']) ? $data['vcName'] : '';
		$vcHp = isset($data['vcHp']) ? $data['vcHp'] : '';
		$emEvenAlarm = isset($data['emEvenAlarm']) ? $data['emEvenAlarm'] : 'N';
		$vcRecommendCode = isset($data['vcRecommendCode']) ? $data['vcRecommendCode'] : '';

		if(empty($nSeqNo)) $aValidation[] = 'nSeqNo IS NULL';
		if(count($aValidation)>=1){
			json_encode($aValidation);
			exit;
		}

		// DB 업데이트
		$this->db->set('vcPassword', md5($vcPassword));
		$this->db->set('vcName', $vcName);
		$this->db->set('vcHp', $vcHp);
		$this->db->set('dtModifyDate','NOW()',false);
	
		$this->db->set('emEvenAlarm', $emEvenAlarm);
		if($data['emEvenAlarm'] == 'Y') $this->db->set('dtEvenAlarm','NOW()',false);			// 쿠폰/이벤트 알림 받기 (선택)
		if(!empty($vcRecommendCode)) $this->db->set('vcRecommendCode',$vcRecommendCode);		// 추천인코드
		
		$this->db->where('nSeqNo', $nSeqNo);

		$aRtn['result'] = $this->db->update($this->member_table);

		if($aRtn['result'] === false){
			$aRtn['error_type'] = 'update_error';
		}

		return $aRtn;
	}
	
	// 회원 정보 삭제
	function delete($data){
		$aRtn = array();
		$aValidation = array();

		$aRtn['result'] = false;

		$nSeqNo = isset($data['nSeqNo']) ? $data['nSeqNo'] : '';
		
		if(empty($nSeqNo)) $aValidation[] = 'nSeqNo IS NULL';
		if(count($aValidation)>=1){
			json_encode($aValidation);
			exit;
		}

		// DB 삭제
		$this->db->where('nSeqNo', $nSeqNo);
		$this->db->set('emDeleted', 'Y');
		$this->db->set('dtModifyDate','NOW()',false);
		$aRtn['result'] = $this->db->update($this->member_table);

		if($aRtn['result'] === false){
			$aRtn['error_type'] = 'update_error';
		}

		return $aRtn;
	}


	// 회원 조회 
	// nSeqNo 개별조회
	// limit,start 리스트조회
	function member_inquiry($data){
		$limit= isset($data['limit']) ? $data['limit'] : '0';
		$start =  isset($data['start']) ? $data['start'] : '0';

		$this->db->from($this->member_table);
		$this->db->where('emDeleted','N');
		if(!empty($data['nSeqNo'])) $this->db->where('nSeqNo',$data['nSeqNo']);
		$this->db->select('nSeqNo,vcEmail,vcName,vcHp');
		$this->db->order_by('nSeqNo','DESC');
		if(!empty($limit)) $this->db->limit($limit,$start);
		$data['list'] = $this->db->get()->result_array();
		return $data ;
	}

	// 전체 회원수 조회
	function member_count(){
		return $data['count'] = $this->db->where('emDeleted','N')->from($this->member_table)->count_all_results();

	}

	function create_member_table(){
		$sQuery = "
			CREATE TABLE `tbl_member` (
			  `nSeqNo` int(10) NOT NULL AUTO_INCREMENT COMMENT '고유값',
			  `vcEmail` varchar(100) DEFAULT NULL COMMENT '이메일',
			  `vcPassword` varchar(255) DEFAULT NULL COMMENT '비밀번호',
			  `vcName` varchar(50) DEFAULT NULL COMMENT '이름',
			  `vcHp` varchar(20) DEFAULT NULL COMMENT '전화번호',
			  `dtRegDate` datetime DEFAULT NULL COMMENT '등록일',
			  `dtModifyDate` datetime DEFAULT NULL COMMENT '수정일',
			  `emUseAgree` enum('Y','N') DEFAULT 'Y' COMMENT '이용약관동의',
			  `dtUseAgree` datetime DEFAULT NULL COMMENT '이용약관동의',
			  `emPersonAgree` enum('Y') DEFAULT 'Y' COMMENT '개인정보 취급방침 동의',
			  `dtPersonAgree` datetime DEFAULT NULL COMMENT '개인정보 취급방침 동의',
			  `emEvenAlarm` enum('Y','N') DEFAULT 'N' COMMENT '쿠폰/이벤트 알림 받기 (선택)',
			  `dtEvenAlarm` datetime DEFAULT NULL COMMENT '쿠폰/이벤트 알림 받기 (선택)',
			  `emDeleted` enum('Y','N') DEFAULT 'N'  COMMENT '삭제여부',
			  `vcRecommendCode` varchar(50) DEFAULT NULL COMMENT '추천인코드',
			  PRIMARY KEY (`nSeqNo`),
			  UNIQUE KEY `vcEmail` (`vcEmail`),
			  KEY `emDel` (`emDeleted`)
			) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8
			";
			$this->db->query($sQuery);
	}

}


