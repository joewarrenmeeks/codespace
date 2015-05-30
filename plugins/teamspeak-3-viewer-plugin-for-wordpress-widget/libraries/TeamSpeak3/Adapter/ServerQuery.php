<?php

/**
 * TeamSpeak 3 PHP Framework
 *
 * $Id: ServerQuery.php 2010-01-10 22:52:12 sven $
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package   TeamSpeak3
 * @version   1.0.21-beta
 * @author    Sven 'ScP' Paulsen
 * @copyright Copyright (c) 2010 by Planet TeamSpeak. All rights reserved.
 */

/**
 * Provides low-level methods for ServerQuery communication with a TeamSpeak 3 Server.
 * 
 * @package  TeamSpeak3_Adapter_ServerQuery
 * @category TeamSpeak3_Adapter
 */
class TeamSpeak3_Adapter_ServerQuery extends TeamSpeak3_Adapter_Abstract
{
  /**
   * Stores a singleton instance of the active TeamSpeak3_Node_Host object.
   *
   * @var TeamSpeak3_Node_Host
   */
  protected $host = null;
  
  /**
   * Stores an array with unsupported commands.
   *
   * @var array
   */
  protected $cmds = array("help", "servernotifyregister", "servernotifyunregister");
  
  /**
   * The TeamSpeak3_Adapter_ServerQuery constructor.
   *
   * @param  TeamSpeak3_Transport_Abstract $transport
   * @throws TeamSpeak3_Adapter_Exception
   * @return TeamSpeak3_Adapter_Abstract
   */
  public function __construct(TeamSpeak3_Transport_Abstract $transport)
  {
    $this->transport = $transport;
    $this->transport->setAdapter($this);
    
    if($this->transport->readLine() != TeamSpeak3::READY)
    {
      throw new TeamSpeak3_Adapter_Exception("invalid reply from the server");
    }
  }
  
  /**
   * The TeamSpeak3_Adapter_ServerQuery destructor.
   *
   * @return void
   */
  public function __destruct()
  {
    if($this->transport instanceof TeamSpeak3_Transport_Abstract && $this->transport->isConnected())
    {
      $this->transport->sendLine("quit");
      $this->transport->readLine();
    }
  }
  
  /**
   * Sends a prepared command to the server and returns the result.
   *
   * @param  string $cmd
   * @throws TeamSpeak3_Adapter_Exception
   * @return TeamSpeak3_Adapter_ServerQuery_Reply
   */
  public function request($cmd)
  {
    if(strstr($cmd, "\r") || strstr($cmd, "\n"))
    {
      throw new TeamSpeak3_Adapter_Exception("illegal characters in command '" . $command . "'");
    }
    
    if(in_array(TeamSpeak3_Helper_String::factory($cmd)->section(TeamSpeak3::SEPERATOR_CELL), $this->cmds))
    {
      throw new TeamSpeak3_Adapter_ServerQuery_Exception("command not found", 0x100);
    }
    
    $this->transport->sendLine($cmd);
    
    $str = null;
    $rpl = array();
    
    do {
      $str = $this->transport->readLine();
      $rpl[] = $str;
    } while($str instanceof TeamSpeak3_Helper_String && $str->section(TeamSpeak3::SEPERATOR_CELL) != TeamSpeak3::ERROR);
    
    return new TeamSpeak3_Adapter_ServerQuery_Reply($rpl, $cmd);
  }
  
  /**
   * Uses given parameters and returns a prepared ServerQuery command.
   *
   * @param  string $cmd
   * @param  array $params
   * @return string
   */
  public function prepare($cmd, array $params = array())
  {
    $args = array();
    $cells = array();
    $blocks = array();
    
    foreach($params as $ident => $value)
    {
      $ident = is_numeric($ident) ? "" : strtolower($ident) . TeamSpeak3::SEPERATOR_PAIR;
      
      if(is_array($value))
      {
        $value = array_values($value);
        
        for($i = 0; $i < count($value); $i++)
        {
          if($value[$i] === null) continue;
          elseif($value[$i] === FALSE) $value[$i] = 0x00;
          elseif($value[$i] === TRUE) $value[$i] = 0x01;
          elseif($value[$i] instanceof TeamSpeak3_Node_Abstract) $value[$i] = $value[$i]->getId();
          
          $cells[$i][] = $ident . TeamSpeak3_Helper_String::factory($value[$i])->escape()->toUtf8();
        }
      }
      else
      {
        if($value === null) continue;
        if($value === FALSE) $value = 0x00;
        if($value === TRUE) $value = 0x01;
        
        $args[] = $ident . TeamSpeak3_Helper_String::factory($value)->escape()->toUtf8();
      }
    }
    
    foreach($cells as $ident => $pairs) $cells[$ident] = implode(TeamSpeak3::SEPERATOR_CELL, $cells[$ident]);
    
    if(count($cells)) $cmd .= " " . implode(TeamSpeak3::SEPERATOR_LIST, $cells);
    if(count($args)) $cmd .= " " . implode(TeamSpeak3::SEPERATOR_CELL, $args);
    
    return trim($cmd);
  }
  
  /**
   * Returns the TeamSpeak3_Node_Host object of the current connection.
   *
   * @return TeamSpeak3_Node_Host
   */
  public function getHost()
  {
    if($this->host === null)
    {
      $this->host = new TeamSpeak3_Node_Host($this);
    }
    
    return $this->host;
  }
}
