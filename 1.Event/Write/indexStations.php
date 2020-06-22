#!/usr/bin/php
<?php
$_SERVER['REQUEST_URI']='/';
require_once('/home/EDRO.SetOfTools/System/0.Functions/0.strNDigit.php');
require_once('/home/EDRO.SetOfTools/System/0.Functions/1.RequestsFilter.php');
require_once('/home/EDRO.SetOfTools/System/1.Reporter/0.ReportError.php');
require_once('/home/EDRO.SetOfTools/System/1.Reporter/1.Report.php');
require_once('/home/EDRO.SetOfTools/System/2.VectorKIIM/0.KIIM.php');
require_once('/home/EDRO.SetOfTools/System/2.VectorKIIM/1.objKIIM.activation.php');
require_once('/home/EDRO.SetOfTools/System/3.Buffer/0.EDRO_Loader.php');
require_once('/home/EDRO.SetOfTools/System/3.Buffer/1.EDRO_Buffering.php');

$objIndex =new Полимер();
class Полимер
	{
	private $strDBPath			='/home/EDRO.Полимер';
	private $strDBName			='HiFiIntelligentClub';
	private $strDBTableName			='Stations';
	private $arrDBIndexes			=
		array(
			'name'=>
				array(
					'unordered', 'ordered'
				),
			'genre'=>
				array(
					'unordered', 'ordered'
				),
			'bitrate'=>
				array(
					'unordered', 'ordered'
				),
			'server_type'=>
				array(
					'unordered', 'ordered'
				),
			'EDRO.Table_data'=>
				array(
					'unordered', 'ordered'
				),
			);
	private $strOrderType='unordered';

	private $intCurrentRow			=0;
	private $arrStationObjects		=array();


	public function __construct()
		{
		$this->_getSourceList();
		$this->_CreateDb();
		$this->_CreateTable();
		$this->_CreateIndexes();
		$this->_InsertInitialRecords();
		$this->_ReadTable();
		//$this->_CreateGenres();
		$this->_LinkGenres();
		//print_r($this);

		//$this->_OrderInitialRecords();
		//
		}
	private function _OrderInitialRecords()
		{
		}
	private function _InsertInitialRecords()
		{
		$strBDTablePath		=$this->strDBPath.'/'.$this->strDBName.'/'.$this->strDBTableName.'/EDRO.Table_data'.'/'.$this->strOrderType;
		$arrRecordObjets=$this->arrReadStationObjectsXML();
		foreach($arrRecordObjets as $objStation)
			{
			$this->_WriteRow($objStation);
			$this->intCurrentRow++;
			}
		$this->_WriteTotal($strBDTablePath, $this->intCurrentRow);
		
		}
	private function _LinkGenres()
		{
		$strBDSourceTablePath		=$this->strDBPath.'/'.$this->strDBName.'/'.$this->strDBTableName.'/EDRO.Table_data'.'/'.$this->strOrderType;
		$strBDTargetTableIndexPath	=$this->strDBPath.'/'.$this->strDBName.'/'.$this->strDBTableName.'/genre';
		$arrRows			=scandir($strBDTargetTableIndexPath);
		foreach($arrRows as $strRow)
			{
			if($strRow!='.'&&$strRow!='..')
				{
				$intPosition=0;
				foreach($this->arrStationObjects as $intStationNum=>$objStation)
					{
					if(in_array($strRow, $this->arrGetGenres($objStation)))
						{
						$this->_CreateLink($strBDSourceTablePath.'/'.$objStation->strFileName, $strBDTargetTableIndexPath.'/'.$strRow.'/unordered/'.$intPosition.'.plmr');
						$intPosition++;
						}
					else
						{
						}
					//$this->_CreateDirFromArr($strBDTableIndexPath, $this->arrGetGenres($objStation));
					}
				//$this->arrStationObjects[]=json_decode(file_get_contents($strBDTablePath.'/'.$strRow));
				$this->_WriteTotal($strBDTargetTableIndexPath.'/'.$strRow.'/unordered', $intPosition);
				}
			}
		}
	private function bHasGenre()
		{
		}
	private function arrGetGenres($_objStation)
		{
		$objStation	=$_objStation;
		           unset($_objStation);
		$arr		=array();
		$objStation->genre	=trim($objStation->genre);
		if(empty($objStation->genre))
			{
			return $arr;
			}
		if (preg_match('/[\s]+/', $objStation->genre, $arrMatch)==1)
			{
			$arrData=explode(' ', $objStation->genre);
			foreach($arrData as $strStyle)
				{
				//$strStyle	=strtolower($strStyle);
				if(empty($strStyle))
					{
					echo 'Multiply genre is empty!'."\n";
					}
				else
					{
					$arr[]	=$strStyle;
					}
				}
			}
		else
			{
			if(!is_object($objStation))
				{
				echo 'Error: objStation is not object!'."\n";
				echo'<pre>';
				print_r($objStation);
				echo'</pre>';
				}
			if(empty($objStation->genre))
				{
				//echo 'Single genre is empty!'."\n";
				}
			else
				{
				//$arr[]		=strtolower($objStation->genre);
				$arr[]		=$objStation->genre;
				}
			}
		return $arr;
		}
	private function _CreateGenres()
		{
		$strBDTableIndexPath=$this->strDBPath.'/'.$this->strDBName.'/'.$this->strDBTableName.'/genre';
		foreach($this->arrStationObjects as $objStation)
			{
			$this->_CreateDirFromArr($strBDTableIndexPath, $this->arrGetGenres($objStation));
			}
		}
	private function _ReadTable()
		{
		$strBDTablePath		=$this->strDBPath.'/'.$this->strDBName.'/'.$this->strDBTableName.'/EDRO.Table_data'.'/'.$this->strOrderType;
		$arrRows		=scandir($strBDTablePath);
		foreach($arrRows as $strRow)
			{
			if($strRow!='.'&&$strRow!='..')
				{
				$objStation=json_decode(file_get_contents($strBDTablePath.'/'.$strRow));
				$objStation->strFileName=$strRow;
				$this->arrStationObjects[]=$objStation;
				}
			}
		
		}
	private function _CreateLink($_strSource, $_strTarget)
		{
		if(symlink($_strSource, $_strTarget))
			{
			}
		else
			{
			echo 'Error creating link!'.$_strSource.'->'.$_strTarget."\n";
			}
		}
	private function _getSourceList()
		{
		exec('/home/HiFiIntelligentClub.Ru/tmp/GetCat.php');
		}
	private function arrReadStationObjectsXML()
		{
		$arrXML=FileRead::objXML($objKIIM, strEncode('ZwEpBCxBPAwqBSAJEQsYLwUSByYdBQU8DFo3GUMdChVBFS8AWxolBQ%3D%3D', 'HiFiIntelligentClub', $_strAct='d'));
		return $arrXML;
		}
	private function _CreateDb()
		{
		$strBDPath=$this->strDBPath.'/'.$this->strDBName;
		if(!is_dir($strBDPath))
			{
			mkdir($strBDPath);
			}
		else
			{
			//$objReport=new Report($objKIIM, 'Cant creat DB'.$this->strDBName);
			//echo 'Cant creat DB'.$this->strDBName."\n";
			echo 'DB'.$this->strDBName.', already exist.'."\n";
			}
		}
	private function _CreateTable()
		{
		$strBDTablePath=$this->strDBPath.'/'.$this->strDBName.'/'.$this->strDBTableName;
		if(!is_dir($strBDTablePath))
			{
			mkdir($strBDTablePath);
			}
		else
			{
			//$objReport=new Report($objKIIM, 'Cant creat table: '.$this->strDBTableName.', in database:'.$this->strDBName."\n");
			//echo 'Cant creat table: '.$this->strDBTableName.', in database:'.$this->strDBName."\n";
			echo 'Table: '.$this->strDBTableName.',already exist in database:'.$this->strDBName."\n";
			}
		$this->_CreateDir($strBDTablePath.'/EDRO.Table_data');
		}
	private function _CreateIndexes()
		{
		$strBDTablePath=$this->strDBPath.'/'.$this->strDBName.'/'.$this->strDBTableName;

		foreach($this->arrDBIndexes as $strIndexName=>$arrIndexType)
			{
			$strBDTableIndexPath=$strBDTablePath.'/'.$strIndexName;
			$this->_CreateDir($strBDTableIndexPath);
			foreach($arrIndexType as $strIndexType)
				{
				$strBDTableIndeTypexPath=$strBDTableIndexPath.'/'.$strIndexType;
				$this->_CreateDir($strBDTableIndeTypexPath);
				}
			}
		}
	private function _CreateCompositeIndexes()
		{
		}
	private function _CreateDirFromArr($_strFolder, $_arrPaths)
		{
		foreach($_arrPaths as $strPath)
			{
			$this->_CreateDir($_strFolder.'/'.$strPath);
			$this->_CreateDir($_strFolder.'/'.$strPath.'/unordered');
			$this->_CreateDir($_strFolder.'/'.$strPath.'/ordered');
			}
		}
	private function _CreateDir($_strPath)
		{
		$strPath=$_strPath;
		   unset($_strPath);

		if(!is_dir($strPath))
			{
			if(mkdir($strPath))
				{
				}
			else
				{
				echo 'Error: Can not create '.$strPath."\n";
				}
			}
		if(is_dir($strPath))
			{
			}
		else
			{
			echo 'Error: wrong directory created!  '.$strPath."\n";
			}
		}
	private function _WriteRow($_objData)
		{
		$strObjData=json_encode($_objData);
				  unset($_objData);
		$strBDTablePath		=$this->strDBPath.'/'.$this->strDBName.'/'.$this->strDBTableName.'/EDRO.Table_data'.'/'.$this->strOrderType;
		$strFileToWrite		=$this->intCurrentRow.'.plmr';
		$strWriteFullPath	=$strBDTablePath.'/'.$strFileToWrite;
		if(!is_file($strFileToWrite))
			{
			if(file_put_contents($strWriteFullPath, $strObjData))
				{
				}
			else
				{
				echo 'Error! Can not write! . '.$strFileToWrite."\n";
				}
			}
		else
			{
			echo 'Error! Collision. FIle already exist!! '.$strFileToWrite."\n";
        		}
		}
	private function _WriteTotal($_strPath, $_intQ)
		{
		//$strBDTablePath		=$this->strDBPath.'/'.$this->strDBName.'/'.$this->strDBTableName.'/EDRO.Table_data'.'/'.$this->strOrderType;
		$strFileToWrite		='total.plmr';
		$strWriteFullPath	=$_strPath.'/'.$strFileToWrite;
		if(!is_file($strFileToWrite))
			{
			if(file_put_contents($strWriteFullPath, json_encode(array('intTotal'=>$_intQ))))
				{
				}
			else
				{
				echo 'Error! Can not write! . '.$strFileToWrite."\n";
				}
			}
		else
			{
			echo 'Error! Collision. FIle already exist!! '.$strFileToWrite."\n";
        		}
		}
	private function _LinkRecord()
		{
		}
	}
?>