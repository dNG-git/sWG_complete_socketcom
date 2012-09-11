<?php
//j// BOF

/*n// NOTE
----------------------------------------------------------------------------
secured WebGine
net-based application engine
----------------------------------------------------------------------------
(C) direct Netware Group - All rights reserved
http://www.direct-netware.de/redirect.php?swg

This Source Code Form is subject to the terms of the Mozilla Public License,
v. 2.0. If a copy of the MPL was not distributed with this file, You can
obtain one at http://mozilla.org/MPL/2.0/.
----------------------------------------------------------------------------
http://www.direct-netware.de/redirect.php?licenses;mpl2
----------------------------------------------------------------------------
#echo(sWGsocketcomVersion)#
sWG/#echo(__FILEPATH__)#
----------------------------------------------------------------------------
NOTE_END //n*/
/**
* This file contains the basic SocketCom TCP layer.
*
* @internal   We are using phpDocumentor to automate the documentation process
*             for creating the Developer's Manual. All sections including
*             these special comments will be removed from the release source
*             code.
*             Use the following line to ensure 76 character sizes:
* ----------------------------------------------------------------------------
* @author     direct Netware Group
* @copyright  (C) direct Netware Group - All rights reserved
* @package    sWG
* @subpackage socketcom
* @since      v0.1.00
* @license    http://www.direct-netware.de/redirect.php?licenses;mpl2
*             Mozilla Public License, v. 2.0
*/
/*#ifdef(PHP5n) */

namespace dNG\sWG;
/* #\n*/
/*#use(direct_use) */
use dNG\sWG\directDataHandler;
/* #\n*/

/* -------------------------------------------------------------------------
All comments will be removed in the "production" packages (they will be in
all development packets)
------------------------------------------------------------------------- */

//j// Functions and classes

if ((!defined ("directTcpFunctions"))&&(USE_socket))
{
/**
* This is an abstraction layer for TCP communication (only basic commands to
* start and finish a communication).
*
* @author     direct Netware Group
* @copyright  (C) direct Netware Group - All rights reserved
* @package    sWG
* @subpackage socketcom
* @since      v0.1.00
* @license    http://www.direct-netware.de/redirect.php?licenses;mpl2
*             Mozilla Public License, v. 2.0
*/
class directTcpFunctions extends directDataHandler
{
/**
	* @var string $data_protocol The defined primary session protocol
*/
	protected $data_protocol;
/**
	* @var string $data_result_code This string contains result data or error
	*      details
*/
	protected $data_result_code;

/* -------------------------------------------------------------------------
Extend the class
------------------------------------------------------------------------- */

/**
	* Constructor (PHP5) __construct (directTcpFunctions)
	*
	* @since v0.1.00
*/
	public function __construct ()
	{
		if (USE_debug_reporting) { direct_debug (3,"sWG/#echo(__FILEPATH__)# -tcpFunctions->__construct (directTcpFunctions)- (#echo(__LINE__)#)"); }

/* -------------------------------------------------------------------------
My parent should be on my side to get the work done
------------------------------------------------------------------------- */

		parent::__construct ();

/* -------------------------------------------------------------------------
Informing the system about available functions 
------------------------------------------------------------------------- */

		$this->functions['connect'] = true;
		$this->functions['disconnect'] = true;
		$this->functions['eofCheck'] = true;
		$this->functions['getHandle'] = true;
		$this->functions['getProtocol'] = true;
		$this->functions['resourceCheck'] = true;

		$this->data = NULL;
		$this->data_protocol = "";
		$this->data_result_code = "";
	}
/**
	* Destructor (PHP5) __destruct (direct_db)
	* Closes the socket connection on destruction.
	*
	* @since v0.1.00
*/
	public function __destruct () { $this->disconnect (); }

/**
	* Opens a new session.
	*
	* @param  string $f_server Primary protocol and server URI
	* @param  integer $f_port Destination port number
	* @return boolean True on success
	* @since  v0.1.00
*/
	public function connect ($f_server,$f_port)
	{
		global $direct_settings;
		if (USE_debug_reporting) { direct_debug (5,"sWG/#echo(__FILEPATH__)# -tcpFunctions->connect ($f_server,$f_port)- (#echo(__LINE__)#)"); }

		$f_return = false;

		if (is_resource ($this->data)) { $f_return = false; }
		else
		{
			if (preg_match ("#^(\w+):\/\/(.+?)$#i",$f_server,$f_result_array))
			{
				$this->data = @fsockopen ($f_server,$f_port,$f_err_no,$f_err_msg,$direct_settings['swg_tcp_timeout']);
				$this->data_protocol = $f_result_array[1];
				$this->data_result_code = "";

				if (($f_err_no)||($f_err_msg)||(!is_resource ($this->data)))
				{
					$this->data = NULL;
					$this->data_result_code = "error:$f_err_no:".$f_err_msg;
				}
				else
				{
					$f_return = true;
					@stream_set_blocking ($this->data,0);
					@stream_set_timeout ($this->data,$direct_settings['swg_tcp_timeout']);
				}
			}
		}

		return /*#ifdef(DEBUG):direct_debug (7,"sWG/#echo(__FILEPATH__)# -tcpFunctions->connect ()- (#echo(__LINE__)#)",:#*/$f_return/*#ifdef(DEBUG):,true):#*/;
	}

/**
	* Closes an active session.
	*
	* @return boolean True on success
	* @since  v0.1.00
*/
	public function disconnect ()
	{
		if (USE_debug_reporting) { direct_debug (5,"sWG/#echo(__FILEPATH__)# -tcpFunctions->disconnect ()- (#echo(__LINE__)#)"); }
		$f_return = false;

		if (is_resource ($this->data))
		{
			$f_return = fclose ($this->data);
			$this->data = NULL;
			$this->data_protocol = "";
			$this->data_result_code = "";
		}

		return /*#ifdef(DEBUG):direct_debug (7,"sWG/#echo(__FILEPATH__)# -tcpFunctions->disconnect ()- (#echo(__LINE__)#)",:#*/$f_return/*#ifdef(DEBUG):,true):#*/;
	}

/**
	* Checks if the pointer is at EOF. Note that keep-alive connections never
	* have an EOF. 
	*
	* @return boolean True if EOF (or if no resource isactive)
	* @since  v0.1.00
*/
	public function eofCheck ()
	{
		if (USE_debug_reporting) { direct_debug (7,"sWG/#echo(__FILEPATH__)# -tcpFunctions->eofCheck ()- (#echo(__LINE__)#)"); }

		if (is_resource ($this->data)) { return /*#ifdef(DEBUG):direct_debug (9,"sWG/#echo(__FILEPATH__)# -tcpFunctions->eofCheck ()- (#echo(__LINE__)#)",(:#*/feof ($this->data)/*#ifdef(DEBUG):),true):#*/; }
		else { return /*#ifdef(DEBUG):direct_debug (9,"sWG/#echo(__FILEPATH__)# -tcpFunctions->eofCheck ()- (#echo(__LINE__)#)",:#*/true/*#ifdef(DEBUG):,true):#*/; }
	}

/**
	* Returns the socket (file) pointer.
	*
	* @return mixed File handle on success; false on error
	* @since  v0.1.00
*/
	public function &getHandle ()
	{
		if (USE_debug_reporting) { direct_debug (5,"sWG/#echo(__FILEPATH__)# -tcpFunctions->getHandle ()- (#echo(__LINE__)#)"); }

		if (is_resource ($this->data)) { $f_return =& $this->data; }
		else { $f_return = false; }

		return $f_return;
	}

/**
	* Returns the currently used protocol.
	*
	* @return boolean True if session is active.
	* @since  v0.1.00
*/
	public function getProtocol ()
	{
		if (USE_debug_reporting) { direct_debug (5,"sWG/#echo(__FILEPATH__)# -tcpFunctions->getProtocol ()- (#echo(__LINE__)#)"); }
		return /*#ifdef(DEBUG):direct_debug (7,"sWG/#echo(__FILEPATH__)# -tcpFunctions->getProtocol ()- (#echo(__LINE__)#)",:#*/$this->data_protocol/*#ifdef(DEBUG):,true):#*/;
	}

/**
	* Returns true if the resource is active.
	*
	* @return boolean True if session is active.
	* @since  v0.1.00
*/
	public function resourceCheck ()
	{
		if (USE_debug_reporting) { direct_debug (5,"sWG/#echo(__FILEPATH__)# -tcpFunctions->resourceCheck ()- (#echo(__LINE__)#)"); }
		return /*#ifdef(DEBUG):direct_debug (7,"sWG/#echo(__FILEPATH__)# -tcpFunctions->resourceCheck ()- (#echo(__LINE__)#)",(:#*/is_resource ($this->data)/*#ifdef(DEBUG):),true):#*/;
	}
}

/* -------------------------------------------------------------------------
Mark this class as the most up-to-date one
------------------------------------------------------------------------- */

define ("CLASS_directTcpFunctions",true);

//j// Script specific commands

global $direct_settings;
if (!isset ($direct_settings['swg_tcp_timeout'])) { $direct_settings['swg_tcp_timeout'] = $direct_settings['timeout_core']; }
}

//j// EOF
?>