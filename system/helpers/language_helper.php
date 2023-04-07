<?php
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014 - 2019, British Columbia Institute of Technology
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package	CodeIgniter
 * @author	EllisLab Dev Team
 * @copyright	Copyright (c) 2008 - 2014, EllisLab, Inc. (https://ellislab.com/)
 * @copyright	Copyright (c) 2014 - 2019, British Columbia Institute of Technology (https://bcit.ca/)
 * @license	https://opensource.org/licenses/MIT	MIT License
 * @link	https://codeigniter.com
 * @since	Version 1.0.0
 * @filesource
 */
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * CodeIgniter Language Helpers
 *
 * @package		CodeIgniter
 * @subpackage	Helpers
 * @category	Helpers
 * @author		EllisLab Dev Team
 * @link		https://codeigniter.com/user_guide/helpers/language_helper.html
 */

// ------------------------------------------------------------------------

if ( ! function_exists('lang'))
{
	/**
	 * Lang
	 *
	 * Fetches a language variable and optionally outputs a form label
	 *
	 * @param	string	$line		The language line
	 * @param	string	$for		The "for" value (id of the form element)
	 * @param	array	$attributes	Any additional HTML attributes
	 * @return	string
	 */
	function lang($line, $for = '', $attributes = array())
	{
		$line = get_instance()->lang->line($line);

		if ($for !== '')
		{
			$line = '<label for="'.$for.'"'._stringify_attributes($attributes).'>'.$line.'</label>';
		}

		return $line;
	}
}

//
//
function select( $check, $val, $true = 'selected', $false = NULL ) {

	if( $check == $val )
		return $true;

	return $false;
}



//
//
function getDesc( $va, $str_desc ) {

	if ( empty( $va ) ) {

		return '';
	}

	if ( empty( $str_desc ) )
		return '';

	$arrayReplace = array();
	
	foreach ( $va as $kb => $vb ) {
		
		$arrayReplace['['. $kb .']'] = $vb;
	}

	return str_replace( array_keys( $arrayReplace ), $arrayReplace, $str_desc );

}


//
//
function genJsonSql( $json ) {



	 

	$json = ( array ) $json;

	$sql = '';

	if ( isset( $json['sql'] ) ) {

		$sql = $json['sql'];

		if ( !empty( $json['param'] ) ) {

			$json['param'] = ( array ) $json['param'];

			$loop = 1;
			
			foreach ( $json['param'] as $kb => $vb ) {

				$vb = ( array ) $vb;

				$json['param'][$kb] = NULL;

				if ( isset( $vb['type'] ) ) {

					if ( $vb['type'] == 'func' ) {
						
						//arr( $vb );
						
						$vb['replace'] = $old_data;

						$json['param'][$kb] = call_user_func( $vb['name'], $vb );
						
					}
					else if ( $vb['type'] == 'rq' ) {

						if ( isset( $vb['tb_name'] ) ) {

							if ( !empty( $config ) ) {
						
								$json['param'][$kb] = $_REQUEST[$vb['name']];
							}
							else {

								$json['param'][$kb] = $_REQUEST[$vb['tb_name']][$vb['name']];
							}
						}
						else {

							$json['param'][$kb] = isset( $_REQUEST[$vb['name']] )? $_REQUEST[$vb['name']]: NULL;

						}
					}
					else if ( $vb['type'] == 'parameter' ) {
//$old_data['doc_no'] = 4545;


						$json['param'][$kb] = $old_data[$vb['name']];
						//$json['param'][$kb] = 'sdssdsds';
						//$json['param'][$kb] = 'fdsasdsd';
					}
					else if ( $vb['type'] == 'session' ) {

						if ( $vb['name'] == 'user_id' ) {

							$vb['name'] = Uid;
						}

						if ( is_object( $_SESSION[$vb['name']] ) ) {

							$f = $vb['f'];
							
							$json['param'][$kb] = $_SESSION[$vb['name']]->$f;
						}
						else {

							$json['param'][$kb] = $_SESSION[$vb['name']];
						}
					}
					else if ( $vb['type'] == 'txt' ) {

						++$loop;

						$json['param'][$kb] = $vb['name'];
					}
				}
				else {
					if( isset( $vb[0] ) )
						$json['param'][$kb] = $vb[0];
				}
			}
		}
	}
	
	if( !empty( $json['param'] ) ) {
		
		foreach( $json['param'] as $kp => $vp ) {
			$sql = str_replace( array_keys( $json['param'] ), $json['param'], $sql );
		}
	}
	

	if ( isset( $config ) ) {

		$arr_replace = array(
			'[database]' => DatabaseSac .'.',
			'[tb_main]' => $config->tb_main,
			'[pri_key]' => $config->pri_key,
			'[tb_parent]' => $config->tb_main,
			'[main_prikey]' => $config->pri_key,
			'[parent_config_Id]' => $config->config_id,
			'[FILE_URL]' => FILE_URL
		);
		//arr( $config );
	//	echo $config->tb_main;

		if ( isset( $json['tb_main'] ) ) {
			$arr_replace['[tb_sub]'] = $json['tb_main'];
		}

		$sql = str_replace( array_keys( $arr_replace ), $arr_replace, $sql );

	}

	return $sql;
}




function arr($arr = array())
{
	echo '<pre>';
	print_r($arr);
	
	//exit;
}

//
//
function convertObJectToArray( $data ) {

	if ( empty( $data ) ) {
		return array();
	}

	$keep = array();
	foreach ( $data as $ka => $va ) {
		$keep[$ka] = $va;
	}

	return $keep;
}

function getDb() {
	
	require_once 'application/models/Db_model.php';
	
	return new Db_model();	
}

//
//
function getConfig_( $config_id ) {

	$dao = getDb();
	
	$sql = "
		SELECT
			b.config_columns_id,
			b.config_columns_name,
			a.*
		FROM admin_model_config a
		LEFT JOIN admin_model_config_columns b ON a.config_doc_head_id = b.config_columns_id
		WHERE a.config_id = " . $config_id;


	foreach( $dao->fetchAll( $sql ) as $kres => $res ) {

		$config = json_decode( $res->config_detail );
		
		
		///
		foreach( $config as $kc => $vc ) {
			
			$config->$kc = stripcslashes( $config->$kc );
		}

	//	exit;

		if ( !empty( $config->in_rows_sql ) ) {

			$json = json_decode( $config->in_rows_sql );
			
			$json->sql = isset( $config->row_sql )? $config->row_sql: NULL;

			$config->in_rows_sql = json_encode( $json );
		}

		$config->signalBlocks = !empty( $config->signalBlocks )? json_decode( $config->signalBlocks ) : array();

		$config->database = $res->config_database;

		$config->config_comment = $res->config_comment;

		$config->config_doc_head = $res->config_columns_name;

		$sql = "
			SELECT
				a.config_columns_id,
				a.config_columns_name,
				a.config_columns_w,
				a.config_columns_label,
				a.config_columns_position,
				IF(
					a.config_override_id IS NULL,
					a.config_columns_detail,
					(
						SELECT
							config_columns_detail
						FROM admin_model_config_columns
						WHERE config_columns_id = a.config_override_id
					)
				) as config_columns_detail
			FROM admin_model_config_columns a
			WHERE a.config_id = ". $config_id ."
			ORDER BY a.config_columns_order ASC
		
		";
		


		$keep = array();

		foreach ( $dao->fetchAll( $sql ) as $ka => $va ) {
			

//arr($va->config_columns_id);
//arr( json_decode( stripcslashes( $va->config_columns_detail ) ) )  ;
			$keep[$va->config_columns_name] = json_decode( stripcslashes( $va->config_columns_detail ) );
			
			 
			$keep[$va->config_columns_name]->position = $va->config_columns_position;
		
			
			$keep[$va->config_columns_name]->w = $va->config_columns_w;

			$keep[$va->config_columns_name]->config_columns_id = $va->config_columns_id;

			$keep[$va->config_columns_name]->label = $va->config_columns_label;
		}
		
		//exit;
		
		$config->columns = convertObJectToArray( $keep );

		$config->config_id = $res->config_id;
		
		

		return $config;
	}
	
	return false;
}

	

function getInput( $va, $val, $label, $name_id ) {
	
	$va = convertObJectToArray( $va );
	
	$dao = getDb();
	
	$detail = $val; 
	if ( !empty( $va['input_type'] ) ) {

		$json = json_decode( $va['input_type'] );
		
		if ( $json->type == 'month' ) {
			
			if( empty( $val ) ) {
				
				$sql = "
					SELECT
						date_format( NOW(), '%Y-%m-01' ) as month_year
				";
				
				$val = $dao->fetch( $sql )->month_year;
			}
			
			//
			$ex = explode( '-', $val );
			
			$options = array();
			
			for( $m = 1; $m <= 12; ++$m ) {
				
				$gg = str_pad( $m, 2, 0, STR_PAD_LEFT );
				
				$options[0][] = '<option '. select( $ex[1], $gg, 'selected' ) .' value="'. $gg .'">'. $gg .'</option>';
			}
			

			for( $y = ( $ex[0] - 2 ); $y <= ( $ex[0] + 5 ); ++$y ) {

				$options[1][] = '<option '. select( $ex[0], $y, 'selected' ) .' value="'. $y .'">'. $y .'</option>';
			}

			$detail = $label .'
				<div class="content-my">
					<div style="width:15%;float:left;margin-left:5px;">
						<select class="gMonth2">'. implode( '', $options[0] ) .'</select> 
					</div>

					<div style="width:15%;float:left;margin-left:5px;">
						<select class="gMonth1">'. implode( '', $options[1] ) .'</select> 
					</div>
					<input type="hidden" name="'. $name_id .'" id="'. $name_id .'" value="'. $val .'">
				</div>
			
			';
			
		}
		else if ( $json->type == 'checkbox' ) {
			
//arr( $name_id );
			$detail = $label . '<input '. select( !empty( $val ), true, $txt = 'checked' ) .' value="1" type="checkbox" name="'. $name_id .'" id="'. $name_id .'" /><span class="require"></span>';
		}
		else if ( $json->type == 'file' ) {

			$detail = $label . '<input type="file" name="'. $name_id .'" id="'. $name_id .'" /><span class="require"></span>';

		}
		else {

			$filter = '';

			if ( !empty( $json->filter ) ) {

				$cond = 'HAVING';

				if ( !empty( $json->cond ) ) {
					$cond = 'WHERE';
				}

				$filter = $cond . " " . $json->filter;
			}
			
			//$filter = "AND id NOT IN ( SELECT role_id FROM `role_permission` ) ";


			$sql = str_replace( array(  '%filter;' ), array( $filter ), $json->sql );

			$json->sql = $sql;

			$sql = genJsonSql( $json );
			
			if( isset( $json->replaceSql ) ) {
				
				foreach( $json->replaceSql as $kr => $vr ) {
					
					$gg[$kr] = $vr;
				}
				
				foreach( $res as $kk => $vk ) {
					$gg[$kk] = $vk;
				}
				
				$sql = genCond_( $sql, $gg );
				
			}
			

			$pri_key = explode( '.', $json->pri_key );

			$pri_key = $pri_key[count($pri_key)-1];

			$option = '';

			foreach ( $dao->fetchAll( $sql ) as $kb => $vb ) {
				$desc = getDesc( $vb, $json->desc );

				$option .= '<option '. select( $vb->$pri_key, $val ) .' value="'. $vb->$pri_key .'">'. $desc .'</option>';
			}
//<option value="0">เลือก</option>
			$detail = $label . '<select class="box-edit-line" name="'. $name_id .'" id="'. $name_id .'">'. $option .'</select>';
		}
	}
	
	
	return $detail;

	
}

	
//
//
function getTrOnStep( $columns, $printDoc = false ) {

	$maxRow = 0;
	$c = array();
	$labels = [];
	if(is_array($columns)) foreach ( $columns as $ka => $va ) {

		$va = convertObJectToArray( $va );

		$va['merg'] = 1;

		$va['column_name'] = $ka;

		if ( isset( $va['show'] ) && $va['show'] == 0 )
			continue;
		
		if( $printDoc == true ) {
			if ( isset( $va['show_on_doc'] ) && $va['show_on_doc'] == 0 )
				continue;
			
		}
		
		

		$ex = explode( '|', $va['label'] );

		if ( count( $ex ) > $maxRow )
			$maxRow = count( $ex );

		$c[] = $va;

	}

	for ( $i = ( $maxRow - 1 ); $i >= 0; --$i ) {

		$parents = array();
		foreach ( $c as $kc => $vc ) {

			$kc = $vc['label'];

			if ( !isset( $h[$kc] ) )
				$h[$kc] = 1;

			$ex = explode( '|', $kc );

			$h_ = $h[$kc];
			if ( isset( $ex[$i] ) ) {

				$label = $ex[$i];

				unset( $ex[$i] );

				$vc['label'] = $name = implode( '|', $ex );

			}
			else {

				$label = '';

				$name = $kc;

				$h[$kc] += 1;

			}

			$labels[$i][] = array( 'label' => $label, 'w' => $vc['w'], 'h' => $h_, 'merg' => $vc['merg'] );


			if ( !isset( $parents[$name] ) ) {
				$parents[$name] = $vc;
				$parents[$name]['w'] = 0;
				$parents[$name]['merg'] = 0;
			}

			$parents[$name]['w'] += $vc['w'];

			$parents[$name]['merg'] += $vc['merg'];

		}

		$c = $parents;
	}

	ksort( $labels );

	return $labels;

}


//
//
function getVal( $val, $va, $status = 'r', $res = array(), $comma = ',', $n = NULL, $main_id = NULL, $parent_data = NULL  ) {

	$dao = getDb();
	
	//return $val;

	if ( is_object( $va ) ) {
		$va = convertObJectToArray( $va );
	}

	if( !empty( $va['showOnReady'] ) ) {
		
		return  getDesc( $res, $va['showOnReady'] );
	}
	else if ( isset( $va['inputformat'] ) && $va['inputformat'] == 'help' ) {

		if ( in_array( $va['helpdetail']->type, array( 'look_comment' ) ) ) {

			$val = getDesc( $res, $va['helpdetail']->label );

		}
		else if ( in_array( $va['helpdetail']->type, array( 'help_full', 'multi_check' ) ) ) {
			
			//arr( $va['helpdetail']);
			
			if( !empty( $va['showOnReady'] ) ) {
				
				$val =  getDesc( $res, $va['showOnReady'] );
			}
			else{
				

				$clip = '';
				if( !empty( $va['ref_model_id'] ) ) {

					$sql = "
						SELECT

							b.new_config_id
						FROM admin_model b
						WHERE b.model_id = " . $va['ref_model_id'];

					$ref_view = $dao->fetch( $sql );

					//
					//
					if ( empty( $ref_view->new_config_id ) )
						return '<span class="red">ยังไม่ได้ให้สิทธิ์การใช้งาน </span>';

				//echo $ref_view->new_config_id;
					$ref_config = getConfig_( $ref_view->new_config_id );


					$va['helpdetail']->main_sql = $ref_config->main_sql;

					$va['helpdetail']->pri_key = $ref_config->pri_key;

					$va['helpdetail']->label = $ref_config->label;
				}
				

				$sql_help_detail = str_replace( '[database]', DatabaseSac .'.', $va['helpdetail']->main_sql );
				//echo $sql_help_detail;
				
				//echo '<br>';
				//echo '<br>';
				$explode = explode( ',', $val );

				$sql_help_detail = str_replace( '%filter;', "HAVING ". $va['helpdetail']->pri_key ." IN ( '". implode( "', '", $explode ) ."' )", $sql_help_detail );

				$sql_help_detail = str_replace( '?', "*", $sql_help_detail );

				$sql_help_detail = str_replace( '[having]', '', $sql_help_detail );

				$sql_help_detail = str_replace( '[company_id]', '1', $sql_help_detail );
				
				

				if ( !empty( $va['helpdetail']->more_filter_sql ) ) {

					$more_filter_sql = json_decode( $va['helpdetail']->more_filter_sql );

					//
					//
					if ( !empty( $more_filter_sql->param ) ) {
						$loop = 1;
						foreach ( $more_filter_sql->param as $kb => $vb ) {

							if ( $vb->type == 'session' ) {

								if ( $vb->name == 'user_id' ) {

									$vb->name = Uid;
								}

								if ( is_object( $_SESSION[$vb->name] ) ) {

									$f = $vb->f;

									$keep[$kb] = $_SESSION[$vb->name]->$f;
								}
								else {

									$keep[$kb] = $_SESSION[$vb->name];
								}
							}
							else if ( $vb->type == 'rq' ) {
								if ( isset( $_REQUEST[$vb->name] ) )
									$keep[$kb] = str_replace( ' ', '%', $_REQUEST[$vb->name] );
							}
							else if ( $vb->type == 'parameter' ) {
								$keep[$kb] = $_REQUEST[$vb->name];
							}
							else if ( $vb->type == 'sql' ) {

								$keep[$kb] = $dao->fetch( $vb->name )->t;

							}
							else if ( $vb->type == 'txt' ) {

								++$loop;

								$keep[$kb] = $va->name;
							}
						}
					}
				}
				
				
				$keep['[sort]'] = '';
				
				$keep['[LIMIT]'] = '';

				$sql_help_detail = str_replace( array_keys( $keep ), $keep, $sql_help_detail );
				
				$sql_help_detail = genCond_( $sql_help_detail, array() );
				
	//arr( $sql_help_detail);

				$res = $dao->fetchAll( $sql_help_detail, array( ':val' => $val ) );

				if ( !$res ) {
					$val = '-';
				}
				else {
					$keep = array();
					
					//$maxShow = 3;
					$pri_key = $va['helpdetail']->pri_key;
					
					foreach ( $res as $kb => $vb ) {

						if ( $status == 'r' ) {

							$keep[] = getDesc( $vb, $va['helpdetail']->label ) . '';

						}
						else {

							$delButton = '';
							if( in_array( $va['helpdetail']->type, array( 'multi_check' ) ) ) {

								$delButton = '
									<span class="remove-list-line" data-pri_key="'. $vb->$pri_key .'">
										<span class="glyphicon glyphicon-remove" aria-hidden="true"></span>
									</span>
								';
							}



							$keep[] = '
								<div style="border-bottom: dashed 1px #845353; padding: 4px 0;">

									'. getDesc( $vb, strip_tags( $va['helpdetail']->label ) ) .'

									'. $delButton .'

								</div>
							';
						}
					}

					$val = implode( '', $keep );
					//$val = 'dsddsfsdad';
				}
			
			
				
			}
			
			

		
		}
	}

	if ( isset( $va['forum_on_ready'] ) && $va['forum_on_ready'] != '' && !empty( $res ) ) {

		$keep = array();

		foreach ( $res as $kd => $vd ) {

			$keep['['. $kd .']'] = $vd;
		}

		$keep[','] = '';

		$str = str_replace( array_keys( $keep ), array_values( $keep ), $va['forum_on_ready'] );

		$sql = "
			SELECT
				( ". $str ." ) as t
		";

		$cal = $dao->fetch( $sql );

		if ( $cal ) {

			$val = $cal->t;
		}
		else {
			$val = 0;
		}
	}


	if ( !empty( $va['inputformat'] ) ) {

		if ( $va['inputformat'] == 'short_date' ) {
		

			if ( !empty( $val ) ) {

				if ( is_numeric( strpos( $val, '-' ) ) ) {

					$ex_date = explode( '-', $val );

					if ( isset( $ex_date[1], $ex_date[0], $ex_date[2] ) ) {

						$val =  gettime_( $val, 13, $thaiTime = false, $custom = NULL );
					}
				}
				else {
					$val = '';
				}
			}
		}
		else if ( $va['inputformat'] == 'date' ) {
		

			if ( !empty( $val ) ) {

				if ( is_numeric( strpos( $val, '-' ) ) ) {

					$ex_date = explode( '-', $val );

					if ( isset( $ex_date[1], $ex_date[0], $ex_date[2] ) ) {

						$val = $ex_date[2] . '/' . makeFrontZero( $ex_date[1], $require_zero = 2 ) . '/' . makeFrontZero( $ex_date[0], $require_zero = 2 );
					}
				}
				else {
					$val = '';
				}
			}
		}
		elseif ( $va['inputformat'] == 'money' ) {

			if ( $status == 'r' && $val == 0 ) {

				$val = '-';
			}
			else {
				$dot = 2;
				if ( isset( $va['dot'] ) ) {

					$dot = $va['dot'];
				}

				$val = getNumFormat( $val, $comma, $dot );
			}

		}
		elseif ( $va['inputformat'] == 'percent' ) {

			$val = getStrPercent( $val );
		}
		elseif ( $va['inputformat'] == 'str_percent' ) {

			if ( !is_numeric( strpos( $val, '%' ) ) )
				$val = getNumFormat( $val, $comma, 2 );

			if ( $status == 'r' && $val == '0%' ) {

				$val = '-';
			}
		}
		elseif ( $va['inputformat'] == 'thaidate' ) {

			$val = getThTimeFormat( $val );
		}
		elseif ( $va['inputformat'] == 'comment' ) {

			$val = nl2br( $val );
		}
		elseif ( $va['inputformat'] == 'password' && $status == 'r' ) {

			$val = '';
		}
		elseif ( $va['inputformat'] == 'auto_number' && $status == 'r' ) {
			if( !empty( $va['record'] ) ) {
				
				
				
				$val = makeFrontZero( $n, $va['record'] );
			}
			else {
				
				$val = $n;
			}
		
			 
			
		}
		elseif ( $va['inputformat'] == 'csv' && $status == 'r' ) {

			$val = '<a target="_blank" href="'. FILE_URL . '/' . $val .'?rand='. rand() .'"><span class="glyphicon glyphicon-paperclip" aria-hidden="true"></span></a>';
		}
	}

	if ( $status == 'r' ) {

		if ( !empty( $va['input_type'] ) ) {

			$json = json_decode( $va['input_type'] );
			if ( $json->type == 'month' ) {
				
				$ex = explode( '-', $val );
				
				$val  = ''. $ex[1] .'/'. $ex[0] .'';
			}
			else if ( $json->type == 'select' ) {

				$cond = 'HAVING';
				if ( !empty( $json->cond ) ) {
					$cond = 'WHERE';
				}

				$filter = $cond ." ". $json->pri_key ." = '". $val ."'";

				$sql = str_replace( array(  '%filter;' ), array( $filter ), $json->sql );

				$json->sql = $sql;

				$sql = genJsonSql( $json, $res );
				
				//arr( $json->replaceSql->sdddsds );
				
				if( isset( $json->replaceSql ) ) {
					
					foreach( $json->replaceSql as $kr => $vr ) {
						
						$gg[$kr] = $vr;
					}
					
					foreach( $res as $kk => $vk ) {
						$gg[$kk] = $vk;
					}
					
					$sql = genCond_( $sql, $gg );
					
				}
				
				
				$val  = getDesc( $dao->fetch( $sql ), $json->desc );
	
			}
			else if ( $json->type == 'file' ) {

				if ( !empty( $val ) && file_exists( FILE_FOLDER . '/' . $val ) ) {
$val = '<img class="full-size" data-target="#myModalPop" data-toggle="modal" src="'. setLink( 'file_upload/' . $val ) .'" >';
					//$val = '<img class="full-size" data-target="#myModalPop" data-toggle="modal" src="file_upload/' . $val .'" >';

				}
				else {
					$val = '<img class="full-size" src="'. WIMG( 'noAvatar.png' ) .'">';
				}
			}
			else if ( $json->type == 'checkbox' ) {

				if ( !empty( $val ) ) {

					$val = 'ใช่';

				}
				else {
					$val = 'ไม่ใช่';
				}
			}
		}

		if ( empty( $val ) )
			$val = '';
	}

	$val = stripslashes( $val );
	
	return $val;
}



function ex( $index ) {
	
	
	
	foreach( explode( '/', uri_string() ) as $ke => $ve ) {
		
		$ex[$ke+1] = $ve;
	}
	
	
	return $ex[$index];
}

//
//
function genCond_( $sql, $replace = array() ) {

	$defConditions = array( 'WHERE', 'AND', 'HAVING' );

	$keep = array();
	foreach( $defConditions as $kr => $vr ) {
		$keep['['. $vr .']'] = '';
	}

	foreach( $replace as $kr => $vr ) {

		if( in_array( $kr, $defConditions ) ) {

			if( !empty( $vr ) ) {
				$keep['['. $kr .']'] = $kr ." " . implode( ' AND ', $vr );
			}
			else {

				$keep['['. $kr .']'] = '';
			}
		}
		else {
			$keep['['. $kr .']'] = $vr;

		}
	}

	return str_replace( array_keys( $keep ), $keep, $sql );
}

function genCond_X( $sql, $replace = array() ) {

	$defConditions = array( 'WHERE', 'AND', 'HAVING' );

	$keep = array();
	foreach( $defConditions as $kr => $vr ) {
		$keep['['. $vr .']'] = '';
	}

	foreach( $replace as $kr => $vr ) {

		if( in_array( $kr, $defConditions ) ) {

			if( !empty( $vr ) ) {
				$keep['['. $kr .']'] = $kr ." " . implode( ' AND ', $vr );
			}
			else {

				$keep['['. $kr .']'] = '';
			}
		}
		else {
			$keep['['. $kr .']'] = $vr;

		}
	}

	return str_replace( array_keys( $keep ), $keep, $sql );
}


function permissionBlock( $param = array() ) {
	
	if( isset( $param['message'] ) ){
		
		return '<div style="margin: 10px;background-color: red;color: white;padding: 10px;">'. $param['message'] .'</div>';
	}
	else {
		return '<div style="margin: 10px;background-color: red;color: white;padding: 10px;">(คุณยังไม่ได้รับสิทธิ์ กรุณาติดต่อผู้ดูแลระบบ)</div>';
	}
}


function page404() {
	
	return '
		<!doctype html>
		<!--[if lte IE 9]>
		<html lang="en" class="oldie">
		<![endif]-->
		<!--[if gt IE 9]><!-->
		<html lang="en">
		<!--<![endif]-->
		<head>
		  <meta charset="utf-8">
		  <meta name="viewport" content="width=device-width, initial-scale=1">
		  <title>404 Error Page</title>
		  <link rel="stylesheet" media="all" href="'. base_url( 'assets/css/404-error-page.css' ) .'" />
		</head>
		<body>
		<div id="clouds">
					<div class="cloud x1"></div>
					<div class="cloud x1_5"></div>
					<div class="cloud x2"></div>
					<div class="cloud x3"></div>
					<div class="cloud x4"></div>
					<div class="cloud x5"></div>
				</div>
				<div class="c">
				  <div class="_404">404</div>
				  <hr>
				  <div class="_1">Error</div>
				  <div class="_2">Page Not Found</div>
					<a class="btn" href="'. base_url( '' ) .'">Back to Home</a>
				</div>
		</body>
		</html>
	';
}

//$param['path'] = 'dasfasaddfds';
function getBase64( $param = array() ) {

	$path   = $param['path'];
	$type   = pathinfo($path, PATHINFO_EXTENSION);
	$data   = file_get_contents($path);
	return $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
}


function makeFrontZero( $number, $require_zero = 2 ) {
	
	return str_pad( $number, $require_zero, 0, STR_PAD_LEFT );
	 
}

