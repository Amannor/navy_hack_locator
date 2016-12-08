<?php

/*

$cfg['exceptions_logpath'] = '';

exceptions::sethandler();
exceptions::viewlogs();



*/



/*

Exceptional Code - PART 1
http://devzone.zend.com/node/view/id/666

Exceptional Code - PART 2
http://devzone.zend.com/node/view/id/679

class CommandManagerException extends Exception{} 
class IllegalCommandException extends Exception{} 

throw new CommandManagerException("directory error: $this->cmdDir"); 

try {
	$mgr = new CommandManager();
	$cmd = $mgr->getCommandObject('realcommand');
	$cmd->execute();
} catch (CommandManagerException $e) {
	die($e->getMessage());
} catch (IllegalCommandException $e) {
	error_log($e->getMessage());
	print "attempting recovery\n";
	// perhaps attempt to invoke a default command?
} catch (Exception $e) {
	print "Unexpected exception\n";
	die($e->getMessage());
}

*/

function exception_passthrough($exception) {
	exceptions::handler($exception);
}

function exception_passthrough_js($exception) {
	exceptions::handler_js($exception);
}

class exceptions {

	static public function sethandler() {
		set_exception_handler('exception_passthrough');
	}

	static public function sethandler_js() {
		set_exception_handler('exception_passthrough_js');
	}

	static public function handler($exception) {

		self::savelogentry($exception);

		if (ini_get('error_reporting') >= E_ALL) {

			$exception_html = self::generatehtml($exception);

			self::page_html('Exception Error', $exception_html);

		} else {

			$body_html = <<<EOHTML
<div style="padding: 5px">
	Sorry, an error has occured.  To view error details, please turn on error reporting display.
</div>
EOHTML;

			header('HTTP/1.0 500 Application error');
			//header('Status: 500 Application error');
			self::page_html('Application Error', $body_html);

		}

		exit;

	}

	static public function viewlogs() {
		global $cfg;

		if (file_exists($cfg['exceptions_logpath'])) {

			$fd = fopen($cfg['exceptions_logpath'], "rb");
			$exceptionlog_lines = fread($fd, filesize($cfg['exceptions_logpath']));
			fclose($fd);

			$exceptionlog_lines = rtrim($exceptionlog_lines, "\n");

			$exceptions = explode("\n", $exceptionlog_lines);

			krsort($exceptions);

			$exceptions_html = '';
			$count = 0;
			foreach ($exceptions as $exception_id => $exception_encoded) {

				$exception_serialised = base64_decode($exception_encoded);
				$exception = unserialize($exception_serialised);

				$exceptions_html .= self::generatehtml($exception[1], $exception[0], $exception_id, true);

				if ($count >= 15) {
					break;
				}

				$count++;

			}

			$body_html = <<<EOHTML
{$exceptions_html}
EOHTML;

		} else {
			$body_html = <<<EOHTML
<div style="padding: 5px">
	Exception logs path is not valid / file does not exist.
</div>
EOHTML;
		}

		self::page_html('Exception Error Logs', $body_html);

	}

	static private function page_html($title, $body_html) {

		echo <<<EOHTML
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>

<!--// http://www.search-this.com/2007/03/12/no-margin-for-error/ //-->

<style type="text/css">
  <!--

	html, body{
		margin: 0;
		padding: 0;
	}

	p, ol, ul, dl, dt, dd, h1, h2, h3, h4, h5, h5, h6 {
		margin: 0 0 0 0;
		padding: 0;
	}

	form {
		margin: 0;
		padding: 0;
	}

	/* --- */

	#content {
		font-family: sans-serif;
	}

	h1 {
		background-color: #E6E6E6;
		padding: 3px;
		margin-bottom: 5px;
	}

	dl {
		border: 1px solid #000000;
		padding-left: 5px;
		padding-right: 5px;
		margin: 5px;
	}

	dt {
		font-weight: bold;
		padding: 2px;
	}

	dd {
		margin-left: 6em;
	}

	dl dd dl {
		margin-top: 0px;
		border: 1px dashed #000000;
	}

	dl dd dl pre {
		margin: 0px;
		margin-bottom: 5px;
	}

  -->
</style>

</head>
<body>

<div id="content">

	<h1>{$title}</h1>

{$body_html}

</div>

<script type="text/javascript">
  <!--

	//alert(document.compatMode);

	function showdetail(exceptionid) {

		document.getElementById("trace_detail-" + exceptionid).style.display = "";
		document.getElementById("trace_show-" + exceptionid).style.display = "none";

	}

  //-->
</script>

</body>
</html>

EOHTML;

	}

	static public function handler_js($exception) {

		self::savelogentry($exception);

		header('Status: 500');

		if (ini_get('error_reporting') >= E_ALL) {

			$message = $exception->getmessage();

			echo <<<EOJS
Exception Error: {$message}
EOJS;

		} else {

			echo <<<EOJS
Sorry, an error has occured.  To view error details, please turn on error reporting display.
EOJS;

		}

	}

	static public function savelogentry($exception) {
		global $cfg;

		$excepwithdate = array(date('Y-m-d H:i:s'), $exception);
		$exception_serialized = serialize($excepwithdate);
		$exception_encoded = base64_encode($exception_serialized);

		$file = fopen($cfg['exceptions_logpath'], "ab");
		flock($file, LOCK_EX);
		fwrite($file, $exception_encoded . "\n");
		fclose($file);

	}

	static private function generatehtml($exception, $date='', $exception_id=0, $hidedetail=false) {

		$trace = $exception->gettrace();

		$trace_html = '';
		foreach ($trace as $trace_item) {

			$file_h = htmlentities(isset($trace_item['file']) ? $trace_item['file'] : '');
			$line_h = htmlentities(isset($trace_item['line']) ? $trace_item['line'] : '');
			$function_h = htmlentities(isset($trace_item['function']) ? $trace_item['function'] : '');
			$class_h = htmlentities(isset($trace_item['class']) ? $trace_item['class'] : '');
			$type_h = htmlentities(isset($trace_item['type']) ? $trace_item['type'] : '');

			if ( (isset($trace_item['args'])) && (is_array($trace_item['args'])) ) {

				$args_html = htmlentities(print_r($trace_item['args'], true));

				$args_html = <<<EOHTML
<pre>{$args_html}</pre>
EOHTML;
			} else {
				$args_html = '';
			}

			$trace_html .= <<<EOHTML
			<dl>

				<dt>File:</dt>
				<dd>{$file_h}</dd>

				<dt>Line:</dt>
				<dd>{$line_h}</dd>
				
				<dt>Function:</dt>
				<dd>{$function_h}</dd>

				<dt>Class:</dt>
				<dd>{$class_h}</dd>

				<dt>Type:</dt>
				<dd>{$type_h}</dd>

				<dt>Args:</dt>
				<dd>
{$args_html}
				</dd>

			</dl>\n\n
EOHTML;
		}


		//lib::prh($exception);
		//lib::prh($trace);

		$message_h = nl2br(htmlentities($exception->getmessage()));
		$code_h = htmlentities($exception->getcode());
		$file_h = htmlentities($exception->getfile());
		$line_h = htmlentities($exception->getline());

		if ($hidedetail) {
			$tracedetail_html = <<<EOHTML
		<div id="trace_detail-{$exception_id}" style="display: none">
{$trace_html}
		</div>
		<a id="trace_show-{$exception_id}" href="#trace-{$exception_id}" onclick="showdetail({$exception_id})">Show Detail</a>
EOHTML;
		} else {
			$tracedetail_html = <<<EOHTML
		<div>
{$trace_html}
		</div>
EOHTML;
		}

		if ($date) {

			$date_h = htmlentities($date);

			$date_html = <<<EOHTML
	<dt>Date:</dt>
	<dd>{$date_h}</dd>
EOHTML;
		} else {
			$date_html = '';
		}

		$exception_html = <<<EOHTML

<dl id="trace-{$exception_id}">

{$date_html}

	<dt>Message:</dt>
	<dd>{$message_h}</dd>

	<dt>Code</dt>
	<dd>{$code_h}</dd>

	<dt>File:</dt>
	<dd>{$file_h}</dd>

	<dt>Line:</dt>
	<dd>{$line_h}</dd>

	<dt>Trace:</dt>
	<dd>
{$tracedetail_html}
	</dd>

</dl>

EOHTML;

		return $exception_html;

	}

}

?>