<?
/*****************************************************************************/
/*                                                                           */
/*  Class:      CMP3Split.inc.php                                            */
/*                                                                           */
/*  Content:    Class for splitting MP3 Files                                */
/*                                                                           */
/*  Requires:   NONE              																				   */
/*                                                                           */
/*  Copyrights: This class was created by Felix-Gabriel Gangu                */
/*              for the ForeverNET Media GmbH, Germany.                      */
/*                                                                           */
/*              Questions? Take a look @ http://www.forevernet.de            */
/*              or drop a line to fgangu@forevernet.de!                      */
/*                                                                           */
/*              Please let me know, if you make some modifications!          */
/*              What about a better error handling?                          */
/*                                                                           */
/*  Hints:      For usage see the included file "mp3split.php"               */
/*                                                                           */
/*---------------------------------------------------------------------------*/
/*  Version History:                                                         */
/*                                                                           */
/*  08.09.01  Date of creation                                               */
/*                                                                           */
/*****************************************************************************/

class CMP3Split
{
  // public
  var $iTmpBuffer=1048576;  // This is the buffer we use
  
  // public (Read only!)
  var $cError;              // Last Error is stored here
  
  // private:
  var $__cInputFile;
  var $__cOutputFile;
  var $__iStartTime;        
  var $__iInputFileSize;    
  var $__iInputFileLength;  
  var $__bytePerSec;        
  var $__inputFp;           
  var $__outputFp;          
  var $__iMode;  		        

  /*.............................................................*/ 
  function CMP3Split($cInputFile="", $iInputFileLength="", $cOutputFile="", $iStartTime="", $iMode="")
  {
    $this->__cInputFile=$cInputFile;
    $this->__cOutputFile=$cOutputFile;
    $this->__iStartTime=$iStartTime;
    $this->__iInputFileLength=$iInputFileLength;
    $this->__iMode=$iMode;
  }

	/*.............................................................*/ 
  function Generate()
  {
    $this->__setInputFileSize();    		
    $this->__BytePerSec();          		
    $this->__openInputMp3();        		
    $this->__openOutputMp3();       		
    $this->__makeNewMp3($this->__iMode);
    $this->__closeOutputMp3();      		
    $this->__closeInputMp3();       		
  }
	
	/*.............................................................*/ 
  function __setInputFileSize()
  {
    $this->__iInputFileSize=@filesize($this->__cInputFile);
  }

	/*.............................................................*/ 
  function __BytePerSec()
  {
    $this->__bytePerSec=(integer)($this->__iInputFileSize/$this->__iInputFileLength);
  }
	
	/*.............................................................*/     
  function __makeNewMp3($iMode)
  {
    $iByteAnz=$this->__iStartTime*$this->__bytePerSec; 
    
    if ($iMode==1)	
    {
	    fseek($this->__inputFp,$iByteAnz);                  
	    while (!@feof($this->__inputFp) )
	        @fwrite($this->__outputFp, @fread($this->__inputFp, $this->iTmpBuffer));
    }
    else						
    {
    	while(@ftell($this->__inputFp)<=((integer)$iByteAnz))
    			@fwrite($this->__outputFp, @fread($this->__inputFp, ($this->__bytePerSec/2)));
    }
  }
  
  /*.............................................................*/ 
  function __openInputMp3()
  {
    if (file_exists($this->__cInputFile))
    {
      $this->__inputFp=fopen($this->__cInputFile,"r");
      $lRet=TRUE;
    }
    else
    {
      $this->__error("Could not open File: ".$this->__cInputFile);
      $lRet=FALSE;
    }
    return $lRet;  
  }
  
  /*.............................................................*/ 
  function __closeInputMp3()
  {
    if (@fclose($this->__inputFp))
    {
      $lRet=TRUE;
    }
    else
    {
      $this->__error("An error occured when trying to close the File  
                    ".$this->__cInputFile.". Something seems to be wrong with the Filepointer");
      $lRet=FALSE;
    }
    return $lRet;  
  }
	
	/*.............................................................*/ 
  function __openOutputMp3()
  {
    $this->__outputFp=@fopen($this->__cOutputFile,"w");
    if ($this->__outputFp)
      $lRet=TRUE;
    else
    {
      $this->__error("Could not create File ".$this->__cOutputFile);
      $lRet=FALSE;
    }
    return $lRet;  
  }
  
  /*.............................................................*/ 
  function __closeOutputMp3()
  {
    if (@fclose($this->__outputFp))
    {
      $lRet=TRUE;
    }
    else
    {
      $this->__error("An Error occured when trying to close the File  
                    ".$this->__cOutputFile."  Something seems to be wrong with the Filepointer!");
      $lRet=FALSE;
    }
    return $lRet;  
  }    

	/*.............................................................*/ 
  function __error($cError)
  {
    $this->cError.=$cError."<br>";
  }
} // EOC
?>