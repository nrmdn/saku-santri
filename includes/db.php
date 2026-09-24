<?php
/**
 * SAKUSANTRI DATABASE PREPARED STATEMENT WRAPPER
 * 100% Parameterized Queries for SQL Injection Prevention
 */

require_once __DIR__ . '/security.php';

/**
 * Deteksi tipe parameter otomatis untuk bind_param
 *
 * @param array $params
 * @return string Contoh: "ssi"
 */
function db_detect_types(array $params) {
    $types = '';
    foreach ($params as $param) {
        if (is_int($param)) {
            $types .= 'i';
        } elseif (is_float($param)) {
            $types .= 'd';
        } else {
            $types .= 's';
        }
    }
    return $types;
}

/**
 * Eksekusi Query Prepared Statement dan kembalikan mysqli_result
 *
 * @param mysqli $koneksi
 * @param string $sql
 * @param array $params
 * @param string $types
 * @return mysqli_result|false
 */
function db_query($koneksi, $sql, array $params = [], $types = '') {
    if (empty($params)) {
        return mysqli_query($koneksi, $sql);
    }

    $stmt = mysqli_prepare($koneksi, $sql);
    if (!$stmt) {
        error_log("DB prepare error: " . mysqli_error($koneksi) . " | SQL: " . $sql);
        return false;
    }

    if (empty($types)) {
        $types = db_detect_types($params);
    }

    mysqli_stmt_bind_param($stmt, $types, ...$params);

    if (!mysqli_stmt_execute($stmt)) {
        error_log("DB execute error: " . mysqli_stmt_error($stmt) . " | SQL: " . $sql);
        mysqli_stmt_close($stmt);
        return false;
    }

    $result = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);

    return $result;
}

/**
 * Ambil 1 baris hasil query sebagai array asosiatif
 *
 * @param mysqli $koneksi
 * @param string $sql
 * @param array $params
 * @param string $types
 * @return array|null
 */
function db_fetch_one($koneksi, $sql, array $params = [], $types = '') {
    $res = db_query($koneksi, $sql, $params, $types);
    if ($res && mysqli_num_rows($res) > 0) {
        return mysqli_fetch_assoc($res);
    }
    return null;
}

/**
 * Ambil seluruh baris hasil query sebagai array
 *
 * @param mysqli $koneksi
 * @param string $sql
 * @param array $params
 * @param string $types
 * @return array
 */
function db_fetch_all($koneksi, $sql, array $params = [], $types = '') {
    $rows = [];
    $res = db_query($koneksi, $sql, $params, $types);
    if ($res && mysqli_num_rows($res) > 0) {
        while ($row = mysqli_fetch_assoc($res)) {
            $rows[] = $row;
        }
    }
    return $rows;
}

/**
 * Eksekusi INSERT / UPDATE / DELETE dengan Prepared Statement
 *
 * @param mysqli $koneksi
 * @param string $sql
 * @param array $params
 * @param string $types
 * @return array ['success' => bool, 'affected_rows' => int, 'insert_id' => int]
 */
function db_execute($koneksi, $sql, array $params = [], $types = '') {
    if (empty($params)) {
        $success = mysqli_query($koneksi, $sql);
        return [
            'success'       => (bool)$success,
            'affected_rows' => mysqli_affected_rows($koneksi),
            'insert_id'     => mysqli_insert_id($koneksi)
        ];
    }

    $stmt = mysqli_prepare($koneksi, $sql);
    if (!$stmt) {
        error_log("DB execute error: " . mysqli_error($koneksi) . " | SQL: " . $sql);
        return ['success' => false, 'affected_rows' => 0, 'insert_id' => 0];
    }

    if (empty($types)) {
        $types = db_detect_types($params);
    }

    mysqli_stmt_bind_param($stmt, $types, ...$params);
    $exec = mysqli_stmt_execute($stmt);

    $affected_rows = mysqli_stmt_affected_rows($stmt);
    $insert_id     = mysqli_stmt_insert_id($stmt);

    mysqli_stmt_close($stmt);

    return [
        'success'       => (bool)$exec,
        'affected_rows' => $affected_rows,
        'insert_id'     => $insert_id
    ];
}

