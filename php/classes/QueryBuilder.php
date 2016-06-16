<?php

class QueryBuilder {

    function __construct() {
        
    }

    function insertUpdate($table, $values1, $values2) {
        $query = $this->insert($table, array_keys($values1));
        $query .= " ON DUPLICATE KEY UPDATE SET ";
        if (count($values2)) {
            $index = 0;
            foreach ($values2 as $key => $value) {
                if ($index > 0) {
                    $query.=" , ";
                }
                $query.="$key = :$value ";
                $index++;
            }
        }
        return $query;
    }

    function update($table, $values, $where = "") {
        $query = "UPDATE $table SET ";

        if (count($values)) {
            $index = 0;
            foreach ($values as $value) {
                if ($index > 0) {
                    $query.=" , ";
                }
                $query.="$value = :$value ";
                $index++;
            }
        }
        if ($where != "") {
            $query.="WHERE $where";
        }
        return $query;
    }

    function updateAssociative($table, $values, $where = "") {
        $query = "UPDATE $table SET ";

        if (count($values)) {
            $index = 0;
            foreach ($values as $key => $value) {
                if ($index > 0) {
                    $query.=" , ";
                }
                $query.="$key = :$value ";
                $index++;
            }
        }
        if ($where != "") {
            $query.="WHERE $where";
        }
        return $query;
    }

    function insert($table, $values) {
        $query = "INSERT INTO " . $table . " ";

        if (count($values) > 0) {
            $query.="(";
            $i = 0;
            foreach ($values as $value) {
                if ($value != "" || $value != null) {
                    if ($i > 0) {
                        $query.=" , ";
                    }
                    $query.=$value;
                    $i++;
                }
            }
            $query.=") VALUES (:";
            $j = 0;
            foreach ($values as $value) {
                if ($value != "" || $value != null) {
                    if ($j > 0) {
                        $query.=" , :";
                    }
                    $query.=$value;
                    $j++;
                }
            }
            $query.=")";
        }
        return $query;
    }

    function selectLike($table, $fields, $values) {
        $query = "SELECT " . $fields . " FROM " . $table . " WHERE ";
        $i = 0;
        foreach ($values as $key => $value) {
            if ($i > 0) {
                $query.=" OR ";
            }
            $query.="$key LIKE '%$value%'";
            $i++;
        }

        return $query;
    }

    function selectLikeSQL($table, $fields, $values) {
        $query = "SELECT " . $fields . " FROM " . $table . " WHERE ";
        $i = 0;
        foreach ($values as $key => $value) {
            if ($i > 0) {
                $query.=" OR ";
            }
            $query.="$key LIKE '%$value%'";
            $i++;
        }
        return $query;
    }

    function innerJoinWhere($table1, $table2, $fields, $on, $where) {
        $query = "SELECT " . $fields . " FROM " . $table1;
        $query.=" INNER JOIN $table2 ON ";
        $query.=$on;
        $query.=" WHERE $where";
        return $query;
    }

    function innerJoinWhereOrderBy($table1, $table2, $fields, $on, $where, $order) {
        $query = "SELECT " . $fields . " FROM " . $table1;
        $query.=" INNER JOIN $table2 ON ";
        $query.=$on;
        $query.=" WHERE $where";
        $query.=" ORDER BY $order DESC";
        return $query;
    }

    function innerJoin($table1, $table2, $fields, $on) {
        $query = "SELECT " . $fields . " FROM " . $table1;
        $query.=" INNER JOIN $table2 ON ";
        $query.=$on;

        return $query;
    }

    function innerJoinOrderBy($table1, $table2, $fields, $on, $order) {
        $query = "SELECT " . $fields . " FROM " . $table1;
        $query.=" INNER JOIN $table2 ON ";
        $query.=$on;
        $query.=" ORDER BY $order DESC";

        return $query;
    }

    function unionSQL($querys) {
        $final = "";
        $i = 0;
        foreach ($querys as $query) {
            if ($i > 0) {
                $final.=" UNION ";
            }
            $final.=$query;
            $i++;
        }
        return $final;
    }

    function union($querys) {
        $final = "";
        $i = 0;
        foreach ($querys as $query) {
            if ($i > 0) {
                $final.=" UNION ";
            }
            $final.=$query;
            $i++;
        }
        return $query;
    }

    function deleteWhere($table, $where) {
        $query = "DELETE  FROM " . $table . " WHERE " . $where;
        return $query;
    }

    function selectFieldsWhere($table, $fields = "*", $where = "TRUE") {
        $query = "SELECT " . $fields . " FROM " . $table . " WHERE " . $where;
        return $query;
    }

    function selectFields($table, $fields) {
        $query = "SELECT " . $fields . " FROM " . $table;
        return $query;
    }

    function selectWhereTop($table, $top = 10, $where = "true") {
        $query = "SELECT * FROM " . $table . " WHERE " . $where . " LIMIT $top";
        return $query;
    }

    function count($table) {
        $query = "SELECT COUNT(*) As 'Count' FROM " . $table;
        return $query;
    }

    function selectWhere($table, $where) {
        $query = "SELECT * FROM " . $table . " WHERE " . $where;
        return $query;
    }

    function selectCountWhere($table, $where) {
        $query = "SELECT COUNT(*) As 'Count' FROM " . $table . " WHERE " . $where . " LIMIT 1";
        echo $query;
        return $query;
    }

    function likeAllWords($field, &$values) {
        $LIKE = "";
        $index = 0;
        foreach ($values as $value) {
            if ($index != 0) {
                $LIKE.=" OR ";
            }
            $LIKE.="$field LIKE '%$value%' ";
            $index++;
        }
        return $LIKE;
    }

}
