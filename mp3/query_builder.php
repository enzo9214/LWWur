<?php

class QueryBuilder
	{
		var $query='';
		var $where_clause='';
		var $query_type=array();
		var $table_list=array();
		var $field_list=array();
		var $value_list=array();
		var $display_fields=array();
		
		function QueryBuilder()
			{
				$this->query='';
				$this->where_clause='';
				$this->query_type=array();
				$this->table_list=array();
				$this->ield_list=array();
				$this->value_list=array();
				$this->display_fields=array();
			}		
		function BuildSelectQuery($field,$value,$is_display_field,$is_string,$table,$operation,$operator)
			{
				if($is_display_field)
					{
						if($field!='*')$this->display_fields[]=$table.".".$field;
						else $this->display_fields[]=$field;
						if(!in_array($table,$this->table_list))$this->table_list[]=$table;							
					}
				else 
					{
						if($is_string)$this->where_clause.=$table.".".$field.$operation."'".$value."' ".$operator." ";
						else $this->where_clause.=$table.".".$field.$operation.$value." ".$operator." ";
					}
			
			}
		function BuildInsertQuery($field,$value,$displayed_in_html,$is_string,$table='')
			{
				if(count($this->table_list)==0 && $table!='')$this->table_list[]=$table;
				
				if($displayed_in_html==true)$value=htmlentities($value,ENT_QUOTES);
				
				$this->field_list[]=$field;
				
				if($is_string)$this->value_list[]="'".$value."'";
				else $this->value_list[]=$value;
			}						
		function BuildUpdateQuery($field,$value,$is_update_field,$displayed_in_html,$is_string,$table,$operation,$operator)
			{
				if($is_update_field)
					{
						if(!in_array($table,$this->table_list))$this->table_list[]=$table;
						if($displayed_in_html==true)$value=htmlentities($value,ENT_QUOTES);
				
						$this->field_list[]=$field;
				
						if($is_string)$this->value_list[]="'".$value."'";
						else $this->value_list[]=$value;
					}
				else
					{
						if($is_string)$this->where_clause.=$table.".".$field.$operation."'".$value."',";
						else $this->where_clause.=$table.".".$field.$operation.$value."',";
					}
			}
		function GetQueryString($use_where_clause)
			{
				if(strtolower($this->query_type)=='insert')
					{
						$this->query="INSERT INTO ".$this->table_list[0]."(";
						
						for($count=0;$count<count($this->field_list);$count++)$this->query.=($this->field_list[$count].",");
						
						$this->query=trim($this->query,',');
						
						$this->query.=") VALUES(";
						
						for($count=0;$count<count($this->value_list);$count++)$this->query.=($this->value_list[$count].",");
						
						$this->query=trim($this->query,',');
						
						$this->query.=")";
					}
				else if(strtolower($this->query_type)=='select')
					{
						if($use_where_clause)$this->where_clause=trim($this->where_clause,',');
						
						$this->query="SELECT ";
						
						for($count=0;$count<count($this->display_fields);$count++)$this->query.=($this->display_fields[$count].",");
						
						$this->query=trim($this->query,',')." FROM ";
						
						for($count=0;$count<count($this->table_list);$count++)$this->query.=($this->table_list[$count].",");
						
						$this->query=trim($this->query,',');
						
						if($use_where_clause)$this->query.=" WHERE ".$this->where_clause;
					}
				else if(strtolower($this->query_type)=='update')
					{
						if($use_where_clause)$this->where_clause=trim($this->where_clause,',');
						
						$this->query="UPDATE ";
						
						for($count=0;$count<count($this->table_list);$count++)$this->query.=($this->table_list[$count].",");
						
						$this->query=trim($this->query,',');
						
						$this->query.=" SET ";
						
						for($count=0;$count<count($this->field_list);$count++)$this->query.=($this->field_list[$count]."=".$this->value_list[$count].",");
						
						$this->query=trim($this->query,',');
						
						if($use_where_clause)$this->query.=" WHERE ".$this->where_clause;
					}
				return $this->query;
			}
		function SetQueryType($type)
			{
				$this->query_type=$type;
			}
		function AddSelectField($field,$table)
			{
				$this->BuildSelectQuery($field,"",true,"",$table,"","");
			}
		function BuildWhereClause($field,$value,$is_string,$table,$operation,$operator)
			{
				$this->BuildSelectQuery($field,$value,false,$is_string,$table,$operation,$operator);
			}
	}
?>