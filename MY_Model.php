<?
class MY_Model extends CI_Model{
		
	 public $db_tabla="";
	 public $db_primary_key="";
	function __construct() {
        parent::__construct();
        $this->load->database();//carga las librerias para manejar db
    }
	
	function get_table($clase=""){
		if($this->db_tabla=='')die("No hay tabla");
		if(is_object($clase)){
			
			if(isset($clase->limit)){
				
				$this->db->limit($clase->limit);
			}
			
		}
		return $this->db->get($this->db_tabla)->result();
	}
	function get_row($id){
		if($this->db_tabla=='' || $this->db_primary_key=='')return;
		$this->get_where($id);
		return $this->db->get($this->db_tabla)->row();
	}
	
	private function get_where($id){
		if($this->db_tabla=='' || $this->db_primary_key=='')return;
			if(gettype($id)=='array')die("Error:  se envio un arreglo se necesita un dato");
				$this->db->where($this->db_primary_key,$id);
			
	
	}
	
	//Acepta un arreglo, o una cadena (la cadena con FALSE FALSE)
	function where($arreglo,$debug=0){
		if(is_object($arreglo)){
			$clase=$arreglo;
			$this->db->select($clase->select,FALSE,FALSE);
			if(isset($clase->where))
			if(is_array($clase->where)){
				$this->db->where($clase->where);
			}else{
				$this->db->where($clase->where,FALSE,FALSE);
				
			}
			if(isset($clase->like) and is_array($clase->like)){
				$this->db->like($clase->like);
			}
			//$this->db->where($clase->where);
			if(isset($clase->group_by)){
				$this->db->group_by($clase->group_by);
			}
			if(isset($clase->joins)){
				if(is_array($clase->joins)){
					foreach($clase->joins as $item){
						if(!isset($item[2])){
							$this->db->join($item[0],$item[1]);
						}else{
							$this->db->join($item[0],$item[1],$item[2]);
						}
					}
				}
			}
			
			if(isset($clase->extra))
			if($clase->extra!=""){
				//extra_no_str
				
				if(!isset($clase->extra_no_str)){
					
					$this->db->where($clase->extra);
				}else{
					if($clase->extra_no_str==FALSE){
						
						$this->db->where($clase->extra,FALSE,FALSE);
					}else{
						
						$this->db->where($clase->extra);	
					}
				}
			}
			if(isset($clase->order_by)){
				$this->db->order_by($clase->order_by);
			}
			if(isset($clase->having)){
			//grupo_actual_desc
				if(is_array($clase->having)){
					$this->db->having($clase->having);
				}else{
					$this->db->having($clase->having,FALSE,FALSE);
				}
			}
			if(isset($clase->limit)){
				
				$this->db->limit($clase->limit);
			}
			$query=$this->db->get($this->db_tabla);			
			if($debug===1 || isset($clase->debug)){
			echo $this->db->last_query();
			}
			return $query->result();
		}
		
		if(is_array($arreglo)){
			$query=$this->db->get_where($this->db_tabla,$arreglo);
		}else{
			$this->db->where($arreglo,FALSE,FALSE);
			$query=$this->db->get($this->db_tabla);
		}
		if($debug===1){
			echo $this->db->last_query();
		}
		return  $query->result();
		
	}
	
	function ajax_sorter($clase){
		$tipo=gettype($clase);
		if($tipo!="object"){
			die("La funcion recibe stdClass");
		}
		if(!isset($clase->select)){
			$clase->select="*";
		}
		if($clase->select==""){
			$clase->select="*";
		}
		$this->db->start_cache();
		if(isset($clase->having)){
			//grupo_actual_desc
			if(is_array($clase->having)){
				$this->db->having($clase->having);
			}else{
				$this->db->having($clase->having,FALSE,FALSE);
			}
		}
		$this->db->select($clase->select." ",FALSE,FALSE);
		if($clase->filter['where']!='')
			$this->db->having($clase->filter['where']);
		
		//para las cosas extra
		if(!isset($clase->extra) && isset($clase->where))$clase->extra=$clase->where;
		if(isset($clase->extra))
		if($clase->extra!=""){
			//extra_no_str
			if(!isset($clase->extra_no_str)){
				
				$this->db->where($clase->extra);
			}else{
				if($clase->extra_no_str==FALSE){
					
					$this->db->where($clase->extra,FALSE,FALSE);
				}else{
					
					$this->db->where($clase->extra);	
				}
			}
		}
		if(isset($clase->joins))
		if(is_array($clase->joins))
		foreach($clase->joins as $item){
			
			if(!isset($item[2])){
				
				$this->db->join($item[0],$item[1]);
				
			}else{
				
				if(!isset($item[3])){
					$this->db->join($item[0],$item[1],$item[2]);
					
				}else{
					$this->db->join($item[0],$item[1],$item[2],$item[3]);
				}
			}
		}
		
		if(isset($clase->group_by)){
			$this->db->group_by($clase->group_by);
		}
		
		$this->db->stop_cache();
		if($clase->filter['order']!='')
		$this->db->order_by($clase->filter['order']);
		//Se verifica si existen condiciones por medio del filtrado, de ser así se considera en la consulta
		If($clase->filter['limit']!=0)
			$result = $this->db->get($this->db_tabla,$clase->filter['limit'],$clase->filter['offset']);
		else //Si no es valida se realiza una consulta general, esto se realiza con propósitos comunes como
			$result = $this->db->get($this->db_tabla);
		
		
		//Se forma el arreglo que sera retornado
		if(isset($clase->debug))
		if($clase->debug==1){
			echo $this->db->last_query();
		}
		
		$return['rows']=$result->result();
		$result = $this->db->get($this->db_tabla);//En este caso no es necesario limitar los registros
		if(isset($clase->debug))
		if($clase->debug==2){
			echo $this->db->last_query();
		}
		$return['num_rows']=$result->num_rows();
		
		return $return;
	}
	
	function set_row($id,$array,$debug=false){
		
		if($id==0){
			//insertar
			$this->db->where($this->db_primary_key,$id);
			$this->db->insert($this->db_tabla,$array);
			if($debug==TRUE){
				echo $this->db->last_query();
			}
			return $this->db->insert_id();
		}else{
			$this->db->where($this->db_primary_key,$id);
			$this->db->update($this->db_tabla,$array);
			if($debug==TRUE){
				echo $this->db->last_query();
			}
			return $id;
		}
	}
	
	function delete($id){
		if(is_array($id)){
			$this->db->where($id);
		}else{
			$this->db->where($this->db_primary_key,$id);
		}
			$this->db->delete($this->db_tabla);
			return $this->db->affected_rows();
		
	}
	

	function delete_where($where){
		if(is_array($where)){
			$this->db->where($where);
		}else{
			$this->db->where($where,FALSE,FALSE);
		}
		$this->db->delete($this->db_tabla);
		return $this->db->affected_rows();
	}

	function where_update($where,$update,$debug=0){
		$query=true;
		if(is_array($where)){
			$this->db->where($where);
			$query=$this->db->update($this->db_tabla,$update);
		}else{
			$this->db->where($where,FALSE,FALSE);
			$query=$this->db->update($this->db_tabla,$update);
		}
		if($debug===1){
			echo $this->db->last_query();
		}
		return  $query;
	}
}
