<?php
namespace Costume\Form;

use Zend\InputFilter\InputFilter;
use Application\TraversableConfig;
use Application\Toolbox\String as StringTools;

class PictureFilter extends InputFilter
{

	public function __construct(TraversableConfig $config)
	{
		$this->add(
				array(
					'name' => 'picture_file',
					'required' => true,
					'validators' => array(
						array(
							'name' => 'File\Size',
							'max' => min(StringTools::return_bytes(ini_get('post_max_size')), 
									StringTools::return_bytes(ini_get('upload_max_filesize')))
						),
/*						array(
							'name' => 'File\MimeType',
							'mimeType' => 'image/jpeg,image/jpg',
							'enableHeaderCheck' => true
						)*/
					),
					'filters' => array(
						array(
							'name' => 'File\RenameUpload',
							'options' => array(
								'target ' => $config->get('data_storage->temp_path', '/tmp') . '/costume_picture',
								'randomize ' => true,
								'overwrite' => true,
								'use_upload_extension' => true
							)
						)
					)
				));
	}
}
