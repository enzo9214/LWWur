<?php
set_time_limit(300000);
include_once('config.inc.php');
include_once('mysql.php');
include_once('query_builder.php');
include_once('set_error_handling.php');

class MP3Management
	{
/******************************************************************************************************************
-Used to Initialise variables.
-Constructor initialises database connection.
/******************************************************************************************************************/
		var $getid3_demo_mysql_encoding = 'ISO-8859-1';
		var $getid3_demo_mysql_md5_data = false;        // All data hashes are by far the slowest part of scanning
		var $getid3_demo_mysql_md5_file = false;
		var $getID3,$getid3_lib;
		
		var $is_debug=true;
		var $is_linux=true;	
		var $duplicate_count=0;
		var $empty_tag_count=0;
		var $total_count=0;
		var $indexed_count=0;
		var $sample_count=0;
		var $total_time=0;
		var $clip_duration=20;							
		var $path_symbol="";
		
		var $album_art_folder="";
		var $clips_folder="";
		var $log_folder="";
		var $original_folder="";			

		function MP3Management()
			{							
				if($this->is_linux)$this->path_symbol="/";
				// Initialize getID3 engine
				$this->getID3 = new getID3;
				$this->getid3_lib = new getid3_lib;
				
				$this->getID3->setOption(array('option_md5_data' => $this->getid3_demo_mysql_md5_data,'encoding'=> $this->getid3_demo_mysql_encoding,));
				
				if($this->is_linux===true)
					{
						//For linux
						$this->path_symbol="/";
						$this->album_art_folder="/home/search/public_html/mp3management/album_art";
						$this->clips_folder="/home/search/public_html/mp3management/clips";
						$this->log_folder="/home/search/public_html/mp3management/logs";
						$this->original_folder="/home3/mp3";
					}
				else
					{
						//For Windows
						$this->path_symbol="\\";
						$this->clips_folder="E:\\Clips\\";
						$this->log_folder="E:\\Log Files\\";
						$this->original_folder="E:\\Original MP3s";
					}
			}
/******************************************************************************************************************
-Used to Display the only html form.
-This is the main function. It performs all the tasks.
/******************************************************************************************************************/				
		function DisplayForm()
			{
				?>
				<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
				<html xmlns="http://www.w3.org/1999/xhtml">
				<head>
				<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
				<title>Process Files</title>							
				</head>
				<body>
				<form action="<?php echo $_SERVER['PHP_SELF']?>" method="POST" name="process_music_files">
					<h2 align="center">Process MP3 Files</h2>
				  <hr>
				  <table width="70%" border="0" cellspacing="5" align="center">
				  	<tr>
						<td width="41%">&nbsp;</td>
						<td width="59%">&nbsp;</td>
					</tr>
				    <tr>
				      <td align="left"><b>Please enter source folder location : </b></td>
				      <td align="left"><input type="text" name="folder_location" id="folder_location" size="50" value="<?php echo isset($_POST['folder_location'])?$_POST['folder_location']:"";?>"/>
				     <input type="button" name="browse" value="Browse" onclick="SelectFolder(2);"/></td>
				    </tr>				    
				    <tr>
				    <td>&nbsp;</td>
				    </tr>
				    <tr>
				     <td width="100%" colspan="2"><input type="checkbox" name="use_file_names" value="1" checked"/> Use tag information in file names (note: file name must be in the format base_path/volume/artist/album/title)</td>
				    </tr>
				    <tr>
				    <td>&nbsp;</td>
				    </tr>
					<tr>
					<td colspan="2" align="center"><input type="submit" name="process_files" value="Process Files"/></td>
					</tr>
				  </table>
				  <br/>
				  <hr>
				  <input type="hidden" name="referer" id="referer" value="a"/>				  
				</form>

				<?php
					if(isset($_POST['process_files']))
						{
							if(!is_dir($_POST['folder_location']))
								{
									$this->DebuggingMessage('<script>alert("Please specify a valid folder name.'.$_POST['folder_location'].'")</script>');											
								}
							else
								{				
									if($this->CopyFolder())
										{						
											$this->GetTags($_POST['use_file_names']);	
											$this->ClipMp3s(); 																						
											$this->UpdateStatistics();	
											$this->GetAlbumArtFromFolders($_POST['folder_location']);																		
										}											
								}
												
						}
					mysql_close();
					?>
		</body>
		</html>
		<?php
			}			
/******************************************************************************************************************
-Used to extract image from id3 tags
/******************************************************************************************************************/
		function ExtractImageFromTags($file_name,$album_name)
			{							
				$mp3_tags = $this->getID3->analyze($file_name);				
				$this->getid3_lib->CopyTagsToComments($mp3_tags);
				ob_start();
				print_r($mp3_tags);
				$mp3_tags=ob_get_contents();
				ob_end_clean();
						
				$frame_type="APIC";
				$index1=strpos($mp3_tags,"[APIC]");		
				if($index1===false){$index1=strpos($mp3_tags,"[PIC]");$frame_type="PIC";}
				if($index1!==false)
					{				
						$index2=strpos($mp3_tags,"[data]",$index1)+10;
						if($frame_type=="APIC")$index3=strpos($mp3_tags,"[datalength]",$index2)-1;
						else if($frame_type=="PIC")$index3=strpos($mp3_tags,"[dataoffset]",$index2)-1;
						$image_data=substr($mp3_tags,$index2,$index3-$index2);
						
						if(strlen($image_data)>50)
							{
								$ext=$this->GetImageExtension($mp3_tags);
								$image_name=str_replace(":"," ",$album_name.$ext);
								$image_name=str_replace("\\"," ",$image_name);
								$image_name=str_replace("/"," ",$image_name);
								$handle = fopen($this->album_art_folder.$this->path_symbol.$image_name, "wb");
								
								fwrite($handle, $image_data);
								fclose($handle);
								$this->DebuggingMessage('(<span style="color: #009900;">Image extracted from tags</span>)');
								return $image_name;
							}
					}			
				$this->DebuggingMessage('(<span style="color: #990099;">Image extraction from tags failed</span>)');
				return false;
			}						
/******************************************************************************************************************
-Used to get album art files in the mp3 folders
/******************************************************************************************************************/
		function GetAlbumArtFromFolders($initial_directory)
			{
				$list_of_files=$this->GetFileNames($initial_directory);
				
				for($count=0;$count<count($list_of_files);$count++)
					{
						$file_name=$list_of_files[$count];
						if(strpos($file_name,".jpg")>0||strpos($file_name,".gif")>0||strpos($file_name,".png")>0)
							{
								$index=strrpos($file_name,$this->path_symbol);
								$image_name=substr($file_name,$index+1);
								$folder_name=substr($file_name,0,$index);								
								copy($file_name,$this->album_art_folder.$this->path_symbol.$image_name);
								$update_str="UPDATE mp_id3_tags SET album_art='".mysql_escape_string($image_name)."' WHERE filename like '".mysql_escape_string($folder_name)."%'";								
								$this->SafeMysqlQuery($update_str);							
							}
					}
			}						
/******************************************************************************************************************
-Used to get the extension of the image file
/******************************************************************************************************************/
		function GetImageExtension($image_data)
			{
				if (preg_match("/jpeg/", $image_data)) {
					return ".jpg";
				} 
				else if	(preg_match("/gif/", $image_data)) {
					return ".gif";
				}
				else if (preg_match("/png/", $image_data) || preg_match("/x-png/", $image_data)) {
					return ".png";
				}
				
				return ".jpg";
			}	
/******************************************************************************************************************
-Used to get the list of all mp3s that have not been clipped.
/******************************************************************************************************************/
        function GetMP3FileNames()
            {
                $list_of_files=array();
                
                include_once('mysql.php');
                include_once('query_builder.php');
                
                $database=new clsTbsSql();
                
                $query_builder=new QueryBuilder();
                
                $query_builder->SetQueryType('select');
            
                $query_builder->AddSelectField("ID",GETID3_DB_TABLE);
                $query_builder->AddSelectField("filename",GETID3_DB_TABLE);
                $query_builder->AddSelectField("playtime_seconds",GETID3_DB_TABLE);
                $query_builder->BuildWhereClause("is_clipped","0",false,GETID3_DB_TABLE,"=","");
                
                $query=$query_builder->GetQueryString(true);
                
                $database->Execute($query);
                
                $db_list=$database->Rows($query);
                
                for($count=0;$count<count($db_list);$count++)
                    {
                        $list_of_files[]=$db_list[$count]["filename"]."*".round($db_list[$count]["playtime_seconds"],2)."*".$db_list[$count]["ID"];
                    }
                return $list_of_files;
            } 			
/******************************************************************************************************************
-Used to produce 20 second clips of all files listed in the database.
-All clipping code occurs in the clip_files.php file. This is third party code and may be.
substituted for more effiecient code. All clipping in done by this function.
-Errors in clipping are saved in a text file whoose name contains the current data and time.
/******************************************************************************************************************/
        function ClipMp3s()
            {                
                include 'clip_files.php';
                
                $stimer = explode(' ', microtime());
                $stimer = $stimer[1] + $stimer[0];
                
                $mp3 = new CMP3Split();    
                $list_of_files=$this->GetMP3FileNames();
                
                echo "Creating MP3 Clips<hr>";
                
                $counter=0;
                for($count=0;$count<count($list_of_files);$count++)
                    {        
                        list($source,$duration,$id)=explode('*',$list_of_files[$count]);
                        
                        $temp_arr=explode($this->path_symbol,$source);
                        $file_name=$temp_arr[count($temp_arr)-1];                        
                        $dest=$this->clips_folder.$this->path_symbol.$file_name;
                        $dir_str=substr($dest,0,strrpos($dest,$this->path_symbol));
                                
                        if(is_file($dest))continue;
                        
                        $this->MakeDirectory($dir_str);   
                        
                        if($duration<=$this->clip_duration)
                            {    
                                if(copy($source,$dest)===true)
                                    {                
                                        $counter++;                                        
                                        echo ($counter).") ".$source;    
                                        echo '(<span style="color: #009900;">OK</span>)<br>';     
                                        $update_str="UPDATE mp_id3_tags SET is_clipped=1 WHERE ID=".$id;                           
                                        SafeMysqlQuery($update_str);
                                        
                                        flush();
                                    }
                            }                
                        else
                            {                                                
                                $mp3 = new CMP3Split($source,$duration,$dest,$this->clip_duration,0);        
                                
                                $mp3->Generate();
                                
                                $err=$mp3->cError;
                                
                                if($err=='')
                                    {
                                        $counter++;                                
                                        echo ($counter).") ".$source;    
                                        echo '(<span style="color: #009900;">OK</span>)<br>';    
                                        flush();
                                    }
                                else
                                    $this->AppendFileName('Error : '.$err,$dest,'c');
                            }
                        unset($mp3);                            
                    }    
                
                $etimer = explode( ' ', microtime() );
                $etimer = $etimer[1] + $etimer[0];
                $time_taken=($etimer-$stimer);
                $this->total_time+=$time_taken;
                
                echo '<hr>Done Clipping!. Time taken: '.round($time_taken,2).' sec<br><br>';                    
            }
            
/******************************************************************************************************************
-Used to create directories in path that do not exist.
/******************************************************************************************************************/
function MakeDirectory($path, $rights = 0777)
	{
		$folder_path = array($path);
	
		while(!@is_dir(dirname(end($folder_path)))
			&& dirname(end($folder_path)) != '/'			
			&& dirname(end($folder_path)) != '')
			array_push($folder_path, dirname(end($folder_path)));
	
		while($parent_folder_path = array_pop($folder_path))
		@mkdir($parent_folder_path, $rights);		
	}            		
/******************************************************************************************************************
-Used to save statistics in the database
/******************************************************************************************************************/
		function UpdateStatistics()
			{												                
                $stimer = explode(' ', microtime());
				$stimer = $stimer[1] + $stimer[0];								
								
				$this->DebuggingMessage('Creating log file. . .');
				$this->DebuggingMessage('(<span style="color: #009900;">OK</span>)<br>');
												
				$etimer = explode(' ',microtime());
				$etimer = $etimer[1] + $etimer[0];
				$time_taken=($etimer-$stimer);
				$this->total_time+=$time_taken;
				
				$this->DebuggingMessage('Creating Summary. . .');
				$database=new clsTbsSql();
				$query_builder=new QueryBuilder();
				$query_builder->SetQueryType('insert');
				$query_builder->BuildInsertQuery('total_count',$this->total_count,false,false,'mp_admin_summary');
				$query_builder->BuildInsertQuery('sample_count',$this->sample_count,false,false,'mp_admin_summary');
				$query_builder->BuildInsertQuery('tagged_count',$this->indexed_count,false,false,'mp_admin_summary');
				$query_builder->BuildInsertQuery('duplicate_count',$this->duplicate_count,false,false,'mp_admin_summary');
				$query_builder->BuildInsertQuery('empty_tag_count',$this->empty_tag_count,false,false,'mp_admin_summary');
				$query_builder->BuildInsertQuery('created_on',date("Y-m-d H:i:s"),false,true,'mp_admin_summary');
				$query_builder->BuildInsertQuery('time_taken',$this->total_time,false,true,'mp_admin_summary');		
				$query=$query_builder->GetQueryString(false);
				$database->Execute($query);
				$this->DebuggingMessage('(<span style="color: #009900;">OK</span>)<br>');	
				
				echo 'Done updating!. Time taken: '.round($time_taken,2).' sec<hr><br><br><br>';	 
			}
/******************************************************************************************************************
-Used to get the latest version of the specified id3 tag
/******************************************************************************************************************/
		function GetId3Tag($tag_array,$tag_name,$is_force)
			{
				if($is_force===false&&isset($tag_array['tags']['id3v2'][$tag_name]))return $tag_array['tags']['id3v2'][$tag_name];
				else if(isset($tag_array['comments'][$tag_name]))return $tag_array['comments'][$tag_name];
				else return "";
			}
			
/******************************************************************************************************************
-Used to get the latest version of the specified id3 tag
/******************************************************************************************************************/
		function GetSpecialId3Tag($tag_array,$tag1_name,$tag2_name)
			{
				if(isset($tag_array[$tag1_name][$tag2_name]))return $tag_array[$tag1_name][$tag2_name];
				else if($tag1_name=="0"&&isset($tag_array[$tag2_name]))return $tag_array[$tag2_name];
			}
/******************************************************************************************************************
-Used to check if the specified id3 tag exists or not
/******************************************************************************************************************/
		function IssetId3Tag($tag_array,$tag_name,$is_array)
			{
				if(!$is_array)
					{
						if(isset($tag_array['tags']['id3v2'][$tag_name])||isset($tag_array['comments'][$tag_name]))return true;
						else return false;
					}
				else
					{
						if(isset($tag_array['tags']['id3v2'][$tag_name][0])||isset($tag_array['comments'][$tag_name][0]))return true;
						else return false;
					}
			}	
/******************************************************************************************************************
-Used to extract id3 tag information from mp3 headers. The tag information is stored in the database.
/******************************************************************************************************************/
		function GetTags($use_file_name)
			{																	
				$stimer = explode(' ', microtime());
				$stimer = $stimer[1] + $stimer[0];
				
				echo 'Scanning all MP3 files in <b>'.realpath($_POST['folder_location']).'</b> (and subdirectories)<hr>';
				flush();
				
				if(!is_array($this->files_to_import))$this->files_to_import=array($this->files_to_import);						
				
				$row_counter = 0;			
				$file_count = count($this->files_to_import);
				
				$this->indexed_count=0;
				$album_art_array=array();
				foreach ($this->files_to_import as $filename) 
					{
						echo '<br>'.date('H:i:s').' ['.number_format(++$row_counter).' / '.number_format($file_count).'] '.$filename;
						
						$ThisFileInfo = $this->getID3->analyze($filename);				
						$this->getid3_lib->CopyTagsToComments($ThisFileInfo);
				
						$album_art="";
						$mp_file_format=$this->GetSpecialId3Tag($ThisFileInfo,'0','fileformat');
						$mp_track_number=$this->GetId3Tag($ThisFileInfo,'track_number',false);
						$mp_track=$this->GetId3Tag($ThisFileInfo,'track',false);
						$mp_title=$this->GetId3Tag($ThisFileInfo,'title',false);
						$mp_genre1=$this->GetId3Tag($ThisFileInfo,'genre',false);
						$mp_genre2=$this->GetSpecialId3Tag($ThisFileInfo,'0','genre');
						$mp_content_type=$this->GetId3Tag($ThisFileInfo,'content_type',false);				
						$mp_album=$this->GetId3Tag($ThisFileInfo,'album',false);
						$mp_year=$this->GetId3Tag($ThisFileInfo,'year',false);
						$mp_bit_rate=$this->GetSpecialId3Tag($ThisFileInfo,'audio','bitrate');
						$mp_playtime_seconds=$this->GetSpecialId3Tag($ThisFileInfo,'0','playtime_seconds');
						$mp_file_path=$this->GetSpecialId3Tag($ThisFileInfo,'0','filenamepath');
						$mp_artist=$this->GetId3Tag($ThisFileInfo,'artist',false);								
						$temp_index=strrpos($filename,".");
						$file_extension=strtolower(substr($filename,$temp_index+1));
								
						if (empty($mp_file_format)||$file_extension=="jpg"||$file_extension=="ini") 
							{	
								echo ' (<span style="color: #990099;">unknown file type</span>)';
								continue;
							}
						else 
							{		
								$this->total_count++;
												
								echo ' (<span style="color: #009900;">OK</span>)';							
								
								$mp_track=$this->GetTrackFromTags($mp_track,$mp_track_number);															
								$mp_title=$this->GetTitleFromTags($mp_title);
								$mp_genre=$this->GetGenreFromTags($ThisFileInfo,$mp_content_type,$mp_genre1,$mp_genre2);																											
								
								if($use_file_name==1)
									{
										$file_details=explode("/",$filename);
										if(count($file_details)==7)
											{
												$mp_artist=$file_details[4];
												$mp_album=$file_details[5];
												$mp_title=$file_details[6];
												$index=strrpos($mp_title,".");
												$mp_title=substr($mp_title,0,$index);
											}
										else if($mp_title=="")
											{
												$mp_title=$file_details[count($file_details)-1];
												$index=strrpos($mp_title,".");
												$mp_title=substr($mp_title,0,$index);
											}
									}
								if(is_array($mp_artist))$mp_artist=implode(",", @$mp_artist);
								$mp_album=is_array($mp_album)?implode(",", $mp_album):$mp_album;
								list($is_added,$existing_file)=$this->IsFileAdded($mp_title,$mp_track,$mp_genre,$mp_playtime_seconds,$mp_artist,$mp_album);		
								
								if($is_added)
									{
										$this->AppendFileName("Error : The file ".$filename. " has already been added. Name of existing file: ".$existing_file.".",$filename.",".$existing_file,'d');
										echo ' (<span style="color: #990099;">File already added. Existing file name: '.$existing_file.'</span>)';
										$this->duplicate_count++;
										continue;
									}					
								
																
								if($mp_artist==""&&$mp_track==""&&$mp_album==""&&$mp_genre=="")
									{
										$this->AppendFileName("Error : The file ".$filename. " has empty tags",$filename,'e');
										echo ' (<span style="color: #990099;">File contains empty tags</span>)';
										$this->empty_tag_count++;									
									}									
								
								if($mp_album!="")
									{
										if(!isset($album_art_array[$mp_album]))
											{
												$album_art=$this->ExtractImageFromTags($filename,$mp_album);
												if($album_art===false)$album_art="";
												$album_art_array[$mp_album]=$album_art;
											}
										else $album_art=$album_art_array[$mp_album];
									}
								if($this->IssetId3Tag($ThisFileInfo,'year',true))$mp_year=$mp_year[0];
								else $mp_year="";																																			
								
								if(!is_numeric($mp_bit_rate))$mp_bit_rate=0;
								
								$database=new clsTbsSql();
								$query_builder=new QueryBuilder();
								$query_builder->SetQueryType('insert');
								$query_builder->BuildInsertQuery('filename',mysql_escape_string($mp_file_path),false,true,GETID3_DB_TABLE);
								$query_builder->BuildInsertQuery('audio_bitrate',mysql_escape_string($mp_bit_rate),false,false,GETID3_DB_TABLE);
								$query_builder->BuildInsertQuery('playtime_seconds',round(mysql_escape_string($mp_playtime_seconds),2),false,false,GETID3_DB_TABLE);
								$query_builder->BuildInsertQuery('artist',mysql_escape_string($mp_artist),true,true,GETID3_DB_TABLE);
								$query_builder->BuildInsertQuery('title',mysql_escape_string($mp_title),true,true,GETID3_DB_TABLE);
								$query_builder->BuildInsertQuery('album',mysql_escape_string($mp_album),true,true,GETID3_DB_TABLE);
								$query_builder->BuildInsertQuery('genre',mysql_escape_string($mp_genre),true,true,GETID3_DB_TABLE);
								$query_builder->BuildInsertQuery('track',mysql_escape_string($mp_track),true,true,GETID3_DB_TABLE);
								$query_builder->BuildInsertQuery('year',mysql_escape_string($mp_year),true,true,GETID3_DB_TABLE);
								$query_builder->BuildInsertQuery('file_type',mysql_escape_string($file_extension),false,true,GETID3_DB_TABLE);
								$query_builder->BuildInsertQuery('created_on',mysql_escape_string(date("Y-m-d H:i:s")),false,true,GETID3_DB_TABLE);
								$query_builder->BuildInsertQuery('album_art',mysql_escape_string($album_art),false,true,GETID3_DB_TABLE);
								
								$query=$query_builder->GetQueryString(false);
								$database->Execute($query);
														
								$this->indexed_count++;
								$mp_genre=$mp_title=$mp_track='';

								if($this->indexed_count%1000==0)
									{
										$etimer = explode( ' ', microtime() );
										$etimer = $etimer[1] + $etimer[0];
										$time_taken=($etimer-$stimer);
										$tagging_rate=$this->indexed_count/$time_taken;
										$time_remaining=($file_count-$this->indexed_count)/$tagging_rate;
										error_log("Tracks ".$this->indexed_count." out of ".$file_count." have been tagged.
										\nTime to completion : ".$time_remaining."sec\nTagging rate : ".$tagging_rate." tracks/sec", 1, "nadir1915@yahoo.com");								
									}
							}
					}
				
				$SQLquery = 'OPTIMIZE TABLE `'.GETID3_DB_TABLE.'`';
				$this->SafeMysqlQuery($SQLquery);
				
				$etimer = explode( ' ', microtime() );
				$etimer = $etimer[1] + $etimer[0];
				$time_taken=($etimer-$stimer);
				$this->total_time+=$time_taken;
				
				echo '<hr>Done inserting id3 tags!. Time taken: '.round($time_taken,2).' sec<br>';		
			} 
/******************************************************************************************************************
-Used to get the genre field from the id3 tags.
/******************************************************************************************************************/
	function GetGenreFromTags($ThisFileInfo,$mp_content_type,$mp_genre1,$mp_genre2)
		{
			$genre="";
			if(isset($ThisFileInfo['tags']['id3v2']['content_type']))$genre=$mp_content_type;
			else if(isset($ThisFileInfo['tags']['id3v2']['genre']))$genre=$mp_genre1;
			else
				{
					if($mp_content_type!="")
						{
							if(is_array($mp_content_type))$genre=mysql_escape_string(trim(implode(",", $mp_content_type)));
							else $genre=mysql_escape_string(trim($mp_content_type));
							
						}
					else
						{
							if(is_array($mp_genre1))$mp_genre1=implode(",", $mp_genre1);
							if(is_array($mp_genre2))$mp_genre2=implode(",", $mp_genre2);
							$genre=mysql_escape_string(trim($mp_genre1,',')).','.mysql_escape_string(trim($mp_genre2,','));
						}
				}							
					
			if(is_array($genre))$genre=mysql_escape_string(trim(@implode(",", $genre),','));
			else $genre=mysql_escape_string(trim($genre,','));
			return $genre;		
		}			
/******************************************************************************************************************
-Used to get the title field from the id3 tags.
/******************************************************************************************************************/
	function GetTitleFromTags($mp_title)
		{
			$this_track_remix = '';
			$this_track_title = '';
			if (!empty($mp_title)) 
				{
					foreach ($mp_title as $possible_title) 
						{
							if (strlen($possible_title) > strlen($this_track_title)) $this_track_title = $possible_title;				
						}
				}
					
			$ParenthesesPairs = array('()', '[]', '{}');
			foreach ($ParenthesesPairs as $pair) 
				{
					if (preg_match_all('/(.*) '.preg_quote($pair{0}).'(([^'.preg_quote($pair).']*[\- '.preg_quote($pair{0}).'])?(cut|dub|edit|version|live|reprise|[a-z]*mix))'.preg_quote($pair{1}).'/iU', $this_track_title, $matches)) 
						{
							$this_track_title = $matches[1][0];
							$this_track_remix = implode("\t", $matches[2]);
						}
				}
				
			return $this_track_title;
		}		
/******************************************************************************************************************
-Used to get the track field from the id3 tags.
/******************************************************************************************************************/
	function GetTrackFromTags($mp_track,$mp_track_number)
		{
			$this_track_track = '';
			if (!empty($mp_track)) 
				{
					foreach ($mp_track as $key => $value) 
						{
							if (strlen($value) > strlen($this_track_track)) $this_track_track = str_pad($value, 2, '0', STR_PAD_LEFT);					
						}
					if (ereg('^([0-9]+)/([0-9]+)$', $this_track_track, $matches)) $this_track_track = str_pad($matches[1], 2, '0', STR_PAD_LEFT).'/'.str_pad($matches[2], 2, '0', STR_PAD_LEFT);				
				}
						
			if ($this_track_track=='' && empty($mp_track_number)==false) 
				{
					foreach ($mp_track_number as $key => $value) 
						{
							if (strlen($value) > strlen($this_track_track)) $this_track_track = str_pad($value, 2, '0', STR_PAD_LEFT);					
						}
					if (ereg('^([0-9]+)/([0-9]+)$', $this_track_track, $matches)) $this_track_track = str_pad($matches[1], 2, '0', STR_PAD_LEFT).'/'.str_pad($matches[2], 2, '0', STR_PAD_LEFT);				
				}
			
			$temp=explode('/',$this_track_track);
			$this_track_track=$temp[0];
			
			return $this_track_track;
		}
/******************************************************************************************************************
-Used to execute the specified mysql query.
/******************************************************************************************************************/
		function SafeMysqlQuery($SQLquery) 
			{
				$result = @mysql_query($SQLquery);
				if (mysql_error()) 
					{
						die('<FONT COLOR="red">'.mysql_error().'</FONT><hr><TT>'.$SQLquery.'</TT>');
					}
				return $result;
			}	
/******************************************************************************************************************
-Used to display a message on the browser.
/******************************************************************************************************************/
		function DebuggingMessage($message)
			{
				if($this->is_debug)echo $message;
			}		
/******************************************************************************************************************
-Used to get the names of all files whoose tags are to be retrieved.
/******************************************************************************************************************/
		function CopyFolder()
			{
				$this->files_to_import=$this->GetFileNames($_POST['folder_location']);
				echo '<span style="color: #009900;">Retrieved the names of all files in '.$_POST['folder_location'].'</span><br/><br>';
				flush();
				return true;
			}
/******************************************************************************************************************
-Used to determine if the specified file was previously added or not.
/******************************************************************************************************************/
		function IsFileAdded($mp_title,$mp_track,$mp_genre,$mp_playtime_seconds,$mp_artist,$mp_album)
			{				
				$database=new clsTbsSql();
				$query_builder=new QueryBuilder();
				$query_builder->SetQueryType('select');
				$query_builder->AddSelectField("*",GETID3_DB_TABLE);
				$query_builder->BuildWhereClause('playtime_seconds',round(mysql_escape_string($mp_playtime_seconds),2),false,GETID3_DB_TABLE,"=","AND");
				$query_builder->BuildWhereClause('artist',mysql_escape_string($mp_artist),true,GETID3_DB_TABLE,"=","AND");
				$query_builder->BuildWhereClause('title',mysql_escape_string($mp_title),true,GETID3_DB_TABLE,"=","AND");
				$query_builder->BuildWhereClause('album',mysql_escape_string($mp_album),true,GETID3_DB_TABLE,"=","AND");
				$query_builder->BuildWhereClause('genre',mysql_escape_string($mp_genre),true,GETID3_DB_TABLE,"=","AND");
				$query_builder->BuildWhereClause('track',mysql_escape_string($mp_track),true,GETID3_DB_TABLE,"=","");								
				$query=$query_builder->GetQueryString(true);			
				$result = $this->SafeMysqlQuery($query);
				
				$row_count=mysql_num_rows($result);										
				$row=mysql_fetch_assoc($result);
		
				if($row_count==0)return array(false,"none");
				else return array(true,$row["filename"]);					
			}
/******************************************************************************************************************
-Used to get the names of all files in the specified folder.
/******************************************************************************************************************/
		function GetFileNames($initial_directory)
			{
				$DirectoriesToScan  = array($initial_directory);
				$DirectoriesScanned = array();
				$FilesInDir=array();
				while (count($DirectoriesToScan) > 0) 
					{
						foreach ($DirectoriesToScan as $DirectoryKey => $startingdir) 
							{
								if ($dir = opendir($startingdir)) 
									{																
										flush();
										while (($file = readdir($dir)) !== false) 
											{
												if (($file != '.') && ($file != '..')) 
													{
														$RealPathName = realpath($startingdir.'/'.$file);
														if (is_dir($RealPathName)) 
															{
																if (!in_array($RealPathName, $DirectoriesScanned) && !in_array($RealPathName, $DirectoriesToScan)) 
																	{
																		$DirectoriesToScan[] = $RealPathName;
																	}
															}
														else if (is_file($RealPathName)) 
															{
																$FilesInDir[] = $RealPathName;
															}
													}
											}
										closedir($dir);
									}
								else 
									{
										echo '<FONT COLOR="RED">Failed to open directory "<b>'.$startingdir.'</b>"</FONT><br><br>';
									}
								$DirectoriesScanned[] = $startingdir;
								unset($DirectoriesToScan[$DirectoryKey]);
							}
					}
				return $FilesInDir;
			}	
/******************************************************************************************************************
-Used to save a message to a text file. The message is also saved to the database
/******************************************************************************************************************/
		function AppendFileName($message,$file_path,$type)
			{	
				$file_name=$this->log_folder.$this->path_symbol.date("Y-m-d").".txt";
				
				if(!is_file($file_name))$fp = fopen($file_name, 'w');
				else $fp = fopen($file_name, 'a');
				
				fwrite($fp, $message."\r\n");	
				fclose($fp);
				
				$SQLquery  = 'INSERT INTO `mp_admin_errors` (`file_name`, `log_type`, `created_on`) VALUES (';
				$SQLquery .= '"'.mysql_escape_string(@$file_path).'", ';
				$SQLquery .= '"'.mysql_escape_string(@$type).'", ';				
				$SQLquery .= '"'.mysql_escape_string(date("Y-m-d H:i:s")).'")';				
				$this->SafeMysqlQuery($SQLquery);
			}
/***************************************************************************************************************************************/
	}
		?>
