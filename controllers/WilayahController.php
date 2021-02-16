<?php

class WilayahController extends BaseController
{
	protected function getIndex($getParams)
	{
		$limit = array_key_exists('limit', $getParams) ? $getParams['limit'] : 10;
		$offset = array_key_exists('offset', $getParams) ? $getParams['offset'] : 0;
		$keyword = array_key_exists('keyword', $getParams) ? $getParams['keyword'] : null;
		$id = array_key_exists('id', $getParams) ? $getParams['id'] : null;
		$parent = array_key_exists('parent', $getParams) ? $getParams['parent'] : null;
		$parent_id = array_key_exists('parent_id', $getParams) ? $getParams['parent_id'] : null;
		$name = array_key_exists('name', $getParams) ? $getParams['name'] : null;
		$display = array_key_exists('display', $getParams) ? $getParams['display'] : null;
		$code = array_key_exists('code', $getParams) ? $getParams['code'] : null;
		$postal_code = array_key_exists('postal_code', $getParams) ? $getParams['postal_code'] : null;
		$level = array_key_exists('level', $getParams) ? $getParams['level'] : null;

		$query = Wilayah::query();
		if (is_null($keyword))
		{
            $this->andWhereExact($query, 'state', Wilayah::STATE_ACTIVE);
			if (!is_null($id)) $this->andWhereId($query, 'id', $id);
			if (!is_null($parent_id)) $this->andWhereId($query, 'parent_id', $parent_id);
			if (!is_null($parent)) $this->andWhereLike($query, 'parent', $parent, false);
			if (!is_null($name)) $this->andWhereLike($query, 'name', $name);
			if (!is_null($display)) $this->andWhereLike($query, 'display', $display);
			if (!is_null($code)) $this->andWhereExact($query, 'code', $code);
			if (!is_null($postal_code)) $this->andWhereExact($query, 'postal_code', $postal_code);
            if (!is_null($level)) $this->andWhereExact($query, 'level', $level);
		}
		else // favors keyword
		{
            $this->andWhereExact($query, 'state', Wilayah::STATE_ACTIVE);
			$this->andWhereKeywords($query, $keyword,
				['name', 'display', 'postal_code']);
		}

        //print_r ($query);
		$result = $query->execute()->toArray();
		return new PaginatedResponse($result, $limit, $offset);
    }
    
    public function index()
    {
        $getParams = $this->request->get();

        return $this->getIndex($getParams);
    }

    public function provinsi()
    {
        $getParams = $this->request->get();
        $getParams['level'] = Wilayah::LEVEL_PROVINSI;

        return $this->getIndex($getParams);
    }

    public function kabupatenkota()
    {
        $getParams = $this->request->get();
        $getParams['level'] = Wilayah::LEVEL_KABUPATENKOTA;

        return $this->getIndex($getParams);
    }

    public function kecamatan()
    {
        $getParams = $this->request->get();
        $getParams['level'] = Wilayah::LEVEL_KECAMATAN;

        return $this->getIndex($getParams);
    }

    public function kelurahandesa()
    {
        $getParams = $this->request->get();
        $getParams['level'] = Wilayah::LEVEL_KELURAHANDESA;

        return $this->getIndex($getParams);
    }
}