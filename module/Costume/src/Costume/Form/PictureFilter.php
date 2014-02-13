<?php
namespace Costume\Form;

use Zend\InputFilter\InputFilter;
use Application\TraversableConfig;

class PictureFilter extends InputFilter
{

	public function __construct(TraversableConfig $config)
	{
		$this->add(
				array(
					'name' => 'picture_file',
					'required' => true,
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
