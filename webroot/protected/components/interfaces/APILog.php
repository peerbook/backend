<?php
class APILog extends CBehavior {
	
	public $groupByToken = true;

	public static $explainLog = array();
	
	public static function logObject($message, $object) {
		
		self::$explainLog[] = array(
			'message' => $message,
			'object' => $object
		);
		
	}
	
	/**
	 * Aggregates the report result.
	 * @param array $result log result for this code block
	 * @param float $delta time spent for this code block
	 * @return array
	 */
	protected function aggregateResult($result,$delta)
	{
		list($token,$calls,$min,$max,$total)=$result;
		if($delta<$min)
			$min=$delta;
		elseif($delta>$max)
		$max=$delta;
		$calls++;
		$total+=$delta;
		return array($token,$calls,$min,$max,$total);
	}
	
	/**
	 * Displays the summary report of the profiling result.
	 * @param array $logs list of logs
	 * @throws CException if Yii::beginProfile() and Yii::endProfile() are not matching
	 */
	protected function logs($logs)
	{
		$stack=array();
		$results=array();
	
		$_messages = array();
	
		foreach($logs as $log)
		{
			if($log[1]!==CLogger::LEVEL_PROFILE) {

				if($log[1] == 'trace' && substr($log[1],0,9) == 'Executing') {
					
				}
				
				$_messages[] = $log;
	
				continue;
			}
			$message=$log[0];
			if(!strncasecmp($message,'begin:',6))
			{
				$log[0]=substr($message,6);
				$stack[]=$log;
			}
			elseif(!strncasecmp($message,'end:',4))
			{
				$token=substr($message,4);
				if(($last=array_pop($stack))!==null && $last[0]===$token)
				{
					$delta=$log[3]-$last[3];
					if(!$this->groupByToken)
						$token=$log[2];
					if(isset($results[$token]))
						$results[$token]=$this->aggregateResult($results[$token],$delta);
					else
						$results[$token]=array($token,1,$delta,$delta,$delta);
				}
				else
					throw new CException(Yii::t('yii','CProfileLogRoute found a mismatching code block "{token}". Make sure the calls to Yii::beginProfile() and Yii::endProfile() be properly nested.',
							array('{token}'=>$token)));
			}
		}
	
		$now=microtime(true);
		while(($last=array_pop($stack))!==null)
		{
			$delta=$now-$last[3];
			$token=$this->groupByToken ? $last[0] : $last[2];
			if(isset($results[$token]))
				$results[$token]=$this->aggregateResult($results[$token],$delta);
			else
				$results[$token]=array($token,1,$delta,$delta,$delta);
		}
	
		$entries=array_values($results);
		$func=create_function('$a,$b','return $a[4]<$b[4]?1:0;');
		usort($entries,$func);
	
		/**
		 * entries
		 * 0 = proc
		 * 1 = count
		 * 2 = min
		 * 3 = max
		 * 4 = total
		*/
	
		$ret = array();
		$ret['summary'] = array(
				'time' => sprintf('%0.5f',Yii::getLogger()->getExecutionTime()),
				'memory' => number_format(Yii::getLogger()->getMemoryUsage()/1024). ' KB'
		);
		$ret['entries'] = array_map(function ($v) {
			return array(
					'query' => $v[0],
					'min' => sprintf('%0.5f',$v[2]),
					'max' => sprintf('%0.5f',$v[3]),
					'total' => sprintf('%0.5f',$v[4]),
					'count' => $v[1]
			);
		}, $entries);
	
		$ret['logs'] = $_messages;
		$ret['explains'] = self::$explainLog;
		
		return $ret;
	}
}