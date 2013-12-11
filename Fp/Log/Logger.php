<?php 
namespace Fp\Log;
use Psr\Log\LoggerInterface;
use Fp\Core\Filter;

class Logger implements LoggerInterface {
	static $instance = null;
	
	/**
	 * @param \Fp\Core\Core $O
	 * @return Logger
	 */
	public static function invoke(\Fp\Core\Core $O) {
		if( !static::$instance ) { 			
			static::$instance = new Logger();
		}
		return static::$instance;
	}
	/**
     * System is unusable.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function emergency($message, array $context = array()) {
    	$this->log('emergency', $message, $context);
    }

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function alert($message, array $context = array()) {
    	$this->log('alert', $message, $context);
    }

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function critical($message, array $context = array()) {
    	$this->log('critical', $message, $context);
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function error($message, array $context = array()) {
    	$this->log('error', $message, $context);
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function warning($message, array $context = array()) {
    	$this->log('warning', $message, $context);
    }

    /**
     * Normal but significant events.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function notice($message, array $context = array()) {    	
    	$this->log('notice', $message, $context);
    }
    
    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function info($message, array $context = array()) {
    	$this->log('info', $message, $context);
    }

    /**
     * Detailed debug information.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function debug($message, array $context = array()) {
    	$this->log('debug', $message, $context);
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return null
     */
    public function log($level, $message, array $context = array()) {   	
    	$errstr=$backtrace=$errno=$errfile=$errline=$code=null;     
      	if( isset($context[0]) && $context[0] instanceof \Exception ) {    		
    		$errstr    = $context[0]->getMessage();
    		$backtrace = $context[0]->getTraceAsString();
    		$errfile   = $context[0]->getFile();
    		$errline   = $context[0]->getLine();
    		$code 	   = $context[0]->getCode();    		
    	}	
    	else if( array_key_exists('exception', $context) AND $context['exception'] instanceof \Exception ) {
    		$errstr    = $context['exception']->getMessage();
    		$backtrace = $context['exception']->getTraceAsString();
    		$errfile   = $context['exception']->getFile();
    		$errline   = $context['exception']->getLine();
    		$code 	   = $context['exception']->getCode();
    	}
    	else {    		
    		$errortype = array (
    				E_ERROR              => 'Erreur',
    				E_WARNING            => 'Alerte',
    				E_PARSE              => 'Erreur d\'analyse',
    				E_NOTICE             => 'Notice',
    				E_CORE_ERROR         => 'Core Error',
    				E_CORE_WARNING       => 'Core Warning',
    				E_COMPILE_ERROR      => 'Compile Error',
    				E_COMPILE_WARNING    => 'Compile Warning',
    				E_USER_ERROR         => 'Erreur spécifique',
    				E_USER_WARNING       => 'Alerte spécifique',
    				E_USER_NOTICE        => 'Note spécifique',
    				E_STRICT             => 'Runtime Notice',
    				E_RECOVERABLE_ERROR  => 'Catchable Fatal Error'
    		);
    		$errno = Filter::int($level);
    		if ( $errno && array_key_exists($errno, $errortype) ) {
    			$level = $errortype[$errno];
    		}
    		$errfile 	= Filter::raw('file', $context);
    		$errline 	= Filter::raw('line', $context);
    		$code=null;
    	}
    	\Fp\Core\Debug::msg($message, $backtrace, $errno, $errfile, $errline, $level);
    	self::$logMessage[] = "<div><b>$level:</b> $message<div>$errfile $errline<pre>$backtrace</pre></div></div>";
    }

    static $logMessage = array();
    public static function getLog() {    	
   		return self::$logMessage;
    }
}