<?php
$mysqli = new mysqli("localhost", "root", "root", "hipaa_stagine");

if ($mysqli->connect_errno) {
    printf("cannot connect %s\n", $mysqli->connect_error);
    exit();
}

$sql = 'select rpa_id, rpa_aqc_id, rp_a_id, rpa_adr_id from remediation_plans_actions inner join remediation_plans rp ON rpa_rp_id = rp_id';

if ($result = $mysqli->query($sql)) {
	while($rpa = $result->fetch_object()){
		$sql1 = 'SELECT aqa_aqo_id, SUM(aqo_risk_score) AS score FROM assessments_questions_answers INNER JOIN assessments_questions AS aq ON aq_id = aqa_aq_id 
		INNER JOIN assessments_questions_categories AS aqc ON aq_aqc_id = aqc_id INNER JOIN assessments_questions_options AS aqo ON aqa_aqo_id = aqo_id 
		WHERE aq_aqc_id = ' . $rpa->rpa_aqc_id . ' AND aqa_a_id = ' . $rpa->rp_a_id . ' AND aqa_adr_id = ' . $rpa->rpa_adr_id;

		if ($result1 = $mysqli->query($sql1)) {
			if ($answerScore = $result1->fetch_object()) {
				$riskLevel = 0;
                if (($answerScore->score >= 6) && ($answerScore->score < 10)) {
                    $riskLevel = 1;
                } elseif ($answerScore->score >= 10) {
                    $riskLevel = 2;
                }

                if ($answerScore->aqa_aqo_id == 3) {
                    $riskLevel = 3;
                }
				$sql2 = 'update remediation_plans_actions set rpa_risk_score = ' . $answerScore->score . ', rpa_risk_level = ' . $riskLevel . ' where rpa_id = ' . $rpa->rpa_id;

				$mysqli->query($sql2);	
			}
			$result1->close();					
		}
	}	
    $result->close();
}

$mysqli->close();