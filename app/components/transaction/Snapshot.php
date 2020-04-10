<?php

namespace App\components\transaction;

class Snapshot
{
    protected array $idList = [];

    protected array $idListGaps = [];

    /**
     * @return array
     */
    public function getIdList(): array
    {
        return $this->idList;
    }

    /**
     * @param array $idList
     * @return $this
     */
    public function setIdList(array $idList): self
    {
        $this->idList = $idList;
        return $this;
    }

    /**
     * @param array $idList
     * @return $this
     */
    public function addIdList(array $idList): self
    {
        //todo optimize, use bit

        $this->idList = array_merge($this->idList, $idList);
        return $this;
    }

    /**
     * @param array $idList
     * @return $this
     */
    public function delIdList(array $idList): self
    {
        $this->idList = array_diff($this->idList, $idList);
        return $this;
    }

    /**
     * @return array
     */
    public function getIdListGaps(): array
    {
        return $this->idListGaps;
    }

    /**
     * @param array $idListGaps
     * @return $this
     */
    public function setIdListGaps(array $idListGaps): self
    {
        $this->idListGaps = $idListGaps;
        return $this;
    }

    /**
     * @param array $idListGaps
     * @return $this
     */
    public function addIdListGaps(array $idListGaps): self
    {
        $this->idListGaps = array_merge($this->idListGaps, $idListGaps);
        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'id_list' => $this->getIdList(),
            'id_list_gaps' => $this->getIdListGaps(),
        ];
    }
}
