<?php
//chdir('..');
include_once './src/Epi.php';
Epi::setPath('base', './src');
Epi::init('api');
Epi::init('route','database');
define('DB_HOST', getenv('OPENSHIFT_MYSQL_DB_HOST'));
define('DB_PORT',getenv('OPENSHIFT_MYSQL_DB_PORT')); 
define('DB_USER',getenv('OPENSHIFT_MYSQL_DB_USERNAME'));
define('DB_PASS',getenv('OPENSHIFT_MYSQL_DB_PASSWORD'));
define('DB_NAME',getenv('OPENSHIFT_GEAR_NAME'));
EpiDatabase::employ('mysql', DB_NAME, DB_HOST, DB_USER, DB_PASS); 


getRoute()->get('/question', 'get_question', EpiApi::external);
getRoute()->post('/question', 'vote', EpiApi::external);
getRoute()->get('/results/?(\d+)?/?(\d+)?', 'get_result_list', EpiApi::external);

getRoute()->run();
/*
 * ******************************************************************************************
 * Define functions and classes which are executed by EpiCode based on the $_['routes'] array
 * ******************************************************************************************
 */

function vote(){
	$item = array();
	if(isset($_POST['selected_situation']) && isset($_POST['question_id'])){
		getDatabase()->execute('SET NAMES utf8');
		$r = getDatabase()->one( 'SELECT count(id) as cnt FROM questions where published_date = date(now()) and id = '.$_POST['question_id'].' limit 1;');
		if($r['cnt'] == 1){
			getDatabase()->execute( 'insert into answers values (null, ' . $_POST['question_id'] . ', "' . $_POST['selected_situation'] . '", now(), "'.$_SERVER['REMOTE_ADDR'].'"); ');
			$item = array ( 'status' => 'ok' );
		}else{
			$item = array ( 'status' => 'fail' );
		}

	}else{

		$item = array ( 'status' => 'fail' );
	}
	return $item;
}

function get_question(){

	getDatabase()->execute('SET NAMES utf8');
	$rs = getDatabase()->one( 'SELECT id, situation_a, situation_b, published_date FROM questions where published_date = date(now()) order by id desc limit 1;');
	$item = array(
			'id' => $rs['id'], 
			'situation_a' => $rs['situation_a'],
			'situation_b' => $rs['situation_b'],
			'published_date' => $rs['published_date']
		);
  return $item;
}


function get_result_list( $count = 10, $page = 0 ){

	getDatabase()->execute('SET NAMES utf8');
	$r = getDatabase()->all( 'SELECT id, situation_a, situation_b, published_date FROM questions where published_date < date(now()) order by id desc limit ' . $page . ', ' . $count );

	$items = array();
	for( $i = 0; $i < count($r) ; $i ++){
		$rs = $r[$i];
		array_push( $items,
			array(
				'id' => $rs['id'], 
				'situation_a' => $rs['situation_a'],
				'situation_b' => $rs['situation_b'],
				'published_date' => $rs['published_date'],
				'cnt_a' => 10,
				'cnt_b' => 123
				)
			);
	}

	$r = getDatabase()->all( 'SELECT question_id , selected_situation,  count(selected_situation) as cnt FROM answers where question_id < (select id from questions where published_date = date(now()) ) group by question_id, selected_situation order by question_id limit ' . $page . ', ' . $count * 2 );

	$counts = array();

	for( $i = 0; $i < count($r) ; $i ++){
		$rs = $r[$i];
		
		for( $j = 0; $j < count($items); $j ++){
			$item = $items[$j];
			if($item['id'] == $rs['question_id']){
				$items[$j]['cnt_'.$rs['selected_situation'] ] = $rs['cnt'];
				break;
			}
		}
	}
	return $items;
}

function showEndpoints()
{
	/*$str="이연재";
	  echo "$str"; str이 출력
	  echo '$str'; 이연재가 출력*/

	echo '빠라바라밤 API <br>';
    echo '<ul>
          <li><a href="/">/</a> -> (home)</li>
          <li><a href="/version">/version</a> -> (print the version of the api)</li>
          <li><a href="/users">/users</a> -> (print each user)</li>
        </ul>';
}

function showVersion(){
	echo 5;
}

function getQuestionList(){
  return array(
    array('질문번호'=> 'Q01','AQuest'=>'검정색 빵이 햄버거','BQuest'=>'검정 국물인 라면','날짜'=>'2015.10.27' ),
    array('질문번호'=> 'Q02','AQuest'=>'세계최고오글말투의 연인','BQuest'=>'모든대답이 단답인 연인','날짜'=>'2015.10.28' ),
    array('질문번호'=> 'Q03','AQuest'=>'시험끝나고는 꿀잠','BQuest'=>'시험끝나고 놀러가자','날짜'=>'2015.10.29' ),
    array('질문번호'=> 'Q04','AQuest'=>'슬플때는 잔잔한노래','BQuest'=>'슬플때는 신나는노래','날짜'=>'2015.10.30' ),
    array('질문번호'=> 'Q05','AQuest'=>'오래달리기 ','BQuest'=>'윗몸일으키기','날짜'=>'2015.10,31' ),
    array('질문번호'=> 'Q06','AQuest'=>'달달한 100일 연애','BQuest'=>'눈빛만봐도 아는 10년 연애','날짜'=>'2015.11.01' ),
    array('질문번호'=> 'Q07','AQuest'=>'매력적인 무쌍','BQuest'=>'예쁜 쌍꺼풀','날짜'=>'2015.11.02' ),
    array('질문번호'=> 'Q08','AQuest'=>'배고픈 하루','BQuest'=>'심심한 하루','날짜'=>'2015.11.03' ),
    array('질문번호'=> 'Q09','AQuest'=>'아이폰','BQuest'=>'갤럭시','날짜'=>'2015.11.04' ),
    array('질문번호'=> 'Q10','AQuest'=>'고대의 아름다움,유럽','BQuest'=>'편안한 휴양지,동남아','날짜'=>'2015.11.05' ),
    array('질문번호'=> 'Q11','AQuest'=>'삻은 과목 먼저','BQuest'=>'좋은 과목 먼저','날짜'=>'2015.11.06' ),
    array('질문번호'=> 'Q12','AQuest'=>'연세대','BQuest'=>'고려대','날짜'=>'2015.11.07' ),
    array('질문번호'=> 'Q13','AQuest'=>'아날로그적인 문자','BQuest'=>'디지털적인 카톡','날짜'=>'2015.11.08' ),
    array('질문번호'=> 'Q14','AQuest'=>'여름의 수영장','BQuest'=>'겨울의 스키장','날짜'=>'2015.11.09' ),
  );
}

function getResult(){
  return array(
     array('결과번호'=> 'R01','ACount'=>'0','BCount'=>'0','날짜'=>'2015.10.27','질문번호'=>'Q01'),
	 array('결과번호'=> 'R02','ACount'=>'0','BCount'=>'0','날짜'=>'2015.10.28','질문번호'=>'Q02'),
     array('결과번호'=> 'R03','ACount'=>'0','BCount'=>'0','날짜'=>'2015.10.29','질문번호'=>'Q03'), 
	  array('결과번호'=> 'R04','ACount'=>'0','BCount'=>'0','날짜'=>'2015.10.30','질문번호'=>'Q04'),
	  array('결과번호'=> 'R05','ACount'=>'0','BCount'=>'0','날짜'=>'2015.10.31','질문번호'=>'Q05'),
	  array('결과번호'=> 'R06','ACount'=>'0','BCount'=>'0','날짜'=>'2015.11.01','질문번호'=>'Q06'),
	  array('결과번호'=> 'R07','ACount'=>'0','BCount'=>'0','날짜'=>'2015.11.02','질문번호'=>'Q07'),
	  array('결과번호'=> 'R08','ACount'=>'0','BCount'=>'0','날짜'=>'2015.11.03','질문번호'=>'Q08'),
	  array('결과번호'=> 'R09','ACount'=>'0','BCount'=>'0','날짜'=>'2015.11.04','질문번호'=>'Q09'),
	  array('결과번호'=> 'R10','ACount'=>'0','BCount'=>'0','날짜'=>'2015.11.05','질문번호'=>'Q10'),
	  array('결과번호'=> 'R11','ACount'=>'0','BCount'=>'0','날짜'=>'2015.11.06','질문번호'=>'Q11'),
	  array('결과번호'=> 'R12','ACount'=>'0','BCount'=>'0','날짜'=>'2015.11.07','질문번호'=>'Q12'), 
	  array('결과번호'=> 'R13','ACount'=>'0','BCount'=>'0','날짜'=>'2015.11.08','질문번호'=>'Q13'), 
	  array('결과번호'=> 'R14','ACount'=>'0','BCount'=>'0','날짜'=>'2015.11.09','질문번호'=>'Q14'),
  );
}

?>