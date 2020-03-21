<?php

namespace Kneub\Services\Ged\Nuxeo\Dto;



class GedPickerDocument {
    
	private $id;
	private $title;
	private $type;
	private $content;
	private $thumbnail;

	
    public function __construct(String $id, String $title, String $type, $content = null, $thumbnail = null) 
    {
        $this->setId($id);
		$this->setTitle($title);
		$this->setType($type);
        $this->setContent($content);
		$this->setThumbnail($thumbnail);
	}

    public function getId() : String 
    {
		return id;
	}

    public function setId(String $id)
    {
		$this->id = $id;
	}

    public function getTitle() : String
    {
		return $title;
	}

    public function setTitle(String $title) 
    {
		$this->title = $title;
	}

    public function getType() : String 
    {
		return $type;
	}

    public function setType(String $type) 
    {
		$this->type = $type;
	}

    public function getContent() 
    {
		return $content;
	}

    public function setContent($content) 
    {
		$this->content = $content;
	}

    public function getThumbnail() 
    {
		return $thumbnail;
	}

    public function setThumbnail($thumbnail) 
    {
		$this->thumbnail = $thumbnail;
	}

}
