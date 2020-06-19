<?php

namespace App\components\transaction;

use SwFwLess\components\utils\bitmap\bitarray\BitIntArr;

class Snapshot
{
    /** @var BitIntArr $idList */
    protected $idList;

    public static function createFromIdSlots($idSlots)
    {
        return (new static())->setIdSlots($idSlots);
    }

    public static function createFromArray($array)
    {
        return (new static())->setIdSlots($array['id_slots']);
    }

    public function getIdList()
    {
        return $this->idList;
    }

    /**
     * @param array $idList
     * @return $this
     */
    public function setIdList(array $idList): self
    {
        $this->idList = new BitIntArr();
        foreach ($idList as $id) {
            $this->idList->add($id);
        }
        return $this;
    }

    public function setIdSlots(array $idSlots): self
    {
        $this->idList = BitIntArr::createFromSlots($idSlots);
        return $this;
    }

    /**
     * @param array $idList
     * @return $this
     */
    public function addIdList(array $idList): self
    {
        if (is_null($this->idList)) {
             $this->idList = new BitIntArr();
        }

        foreach ($idList as $id) {
            $this->idList->add($id);
        }

        return $this;
    }

    /**
     * @param array $idList
     * @return $this
     */
    public function delIdList(array $idList): self
    {
        if (is_null($this->idList)) {
            return $this;
        }

        foreach ($idList as $id) {
            $this->idList->del($id);
        }

        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'id_slots' => $this->getIdList()->getSlots(),
        ];
    }
}
