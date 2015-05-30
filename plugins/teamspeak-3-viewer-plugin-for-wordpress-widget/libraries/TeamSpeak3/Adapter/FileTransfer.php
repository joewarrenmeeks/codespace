<?php

/**
 * TeamSpeak 3 PHP Framework
 *
 * $Id: FileTransfer.php 2010-01-10 22:52:12 sven $
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
 * Provides low-level methods for file transfer communication with a TeamSpeak 3 Server.
 * 
 * @package  TeamSpeak3_Adapter_FileTransfer
 * @category TeamSpeak3_Adapter
 */
class TeamSpeak3_Adapter_FileTransfer extends TeamSpeak3_Adapter_Abstract
{
  /**
   * The TeamSpeak3_Adapter_FileTransfer constructor.
   *
   * @param  TeamSpeak3_Transport_Abstract $transport
   * @throws TeamSpeak3_Adapter_Exception
   * @return TeamSpeak3_Adapter_Abstract
   */
  public function __construct(TeamSpeak3_Transport_Abstract $transport)
  {
    $this->transport = $transport;
    $this->transport->setAdapter($this);
  }
  
  /**
   * The TeamSpeak3_Adapter_FileTransfer destructor.
   *
   * @return void
   */
  public function __destruct()
  {
    $this->transport->disconnect();
  }
  
  /**
   * Sends a valid file transfer key to the server to initialize the file transfer.
   *
   * @param  string $ftkey
   * @throws TeamSpeak3_Adapter_Exception
   * @return void
   */
  protected function init($ftkey)
  {
    if(strlen($ftkey) != 32)
    {
      throw new TeamSpeak3_Adapter_Exception("invalid file transfer key format");
    }
    
    $this->transport->send($ftkey);
  }
  
  /**
   * Sends the content of a file to the server.
   *
   * @param  string  $ftkey
   * @param  integer $seek
   * @param  string  $data
   * @return void
   */
  public function upload($ftkey, $seek, $data)
  {
    $this->init($ftkey);
    
    $seek = intval($seek);
    $size = strlen($data);
    $pack = 4096;
    
    for(;$seek < $size;)
    {
      $rest = $size-$seek;
      $pack = $rest < $pack ? $rest : $pack;  
      $buff = substr($data, $seek, $seek+$pack);
      $seek = $seek+$pack;
      
      $this->transport->send($buff);
    }

    if($seek < $size)
    {
      throw new TeamSpeak3_Adapter_Exception("incomplete file upload (" . $seek . " of " . $size . " bytes)");
    }
  }
  
  /**
   * Returns the content of a downloaded file as a TeamSpeak3_Helper_String object.
   *
   * @param  string  $key
   * @param  integer $size
   * @throws TeamSpeak3_Adapter_Exception
   * @return TeamSpeak3_Helper_String
   */
  public function download($ftkey, $size)
  {
    $this->init($ftkey);

    $buff = new TeamSpeak3_Helper_String("");
    $size = intval($size);
    $pack = 4096;
    
    for($seek = 0;$seek < $size;)
    {
      $rest = $size-$seek;
      $pack = $rest < $pack ? $rest : $pack;   
      $data = $this->transport->read($rest < $pack ? $rest : $pack);
      $seek = $seek+$pack;
      
      $buff->append($data);
    }
    
    if(strlen($buff) != $size)
    {
      throw new TeamSpeak3_Adapter_Exception("incomplete file download (" . count($buff) . " of " . $size . " bytes)");
    }
    
    return $buff;
  }
}
