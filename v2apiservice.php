<?php

class V2ApiService extends Rest
{
    protected $table = "cdr";
    protected $category = 'V2 Api Service';

    public function get($f3, $from_child = true)
    {
        $db = $f3->get('DB');

        $action = $f3->get('REQUEST.action') ?: 'cdr';

        if ($action === 'cdr') {
            $this->getCdrData($f3, $db);
        } elseif ($action === 'player') {
            $this->getPlayerData($f3);
        } elseif ($action === 'cdr-monitor') {
            $this->getCDRMonitor($f3, $db);
        } elseif ($action === 'cdr-search') {
            $this->getCdrDataSearch($f3, $db);
        } elseif ($action === 'dst-monitor') {
            $this->getCdrDataByDst($f3, $db);
        } elseif ($action === 'channels') {
            $this->getChannels();
        } else {
            $this->sendError('Invalid action.', 400);
        }
    }


    private function getCdrDataByDst($f3, $db)
    {
        $dstNumber = $f3->get('REQUEST.dst') ?: 'all';
        $extNumber = $f3->get('REQUEST.ext') ?: 'all';

        if ($dstNumber === "all") {
            $query = $db->exec(
                "SELECT calldate, clid, src, dst, dcontext, channel, dstchannel, disposition, billsec, duration, uniqueid, recordingfile, cnum, cnam
                FROM asteriskcdrdb.cdr
                ORDER BY calldate DESC"
            );
        } else {
            $query = $db->exec(
                "SELECT calldate, clid, src, dst, dcontext, channel, dstchannel, disposition, billsec, duration, uniqueid, recordingfile, cnum, cnam
                FROM asteriskcdrdb.cdr
                WHERE dst = ? and (cnum=? OR cnam=? OR src=?)
                ORDER BY calldate DESC",
                [$dstNumber,$extNumber,$extNumber,$extNumber]
            );
        }

        $export = [];
        foreach ($query as $data) {
            $dateParts = strtotime($data['calldate']);
            $recordingFilePath = sprintf("/%s/%s/%s/%s", date("Y", $dateParts), date("m", $dateParts), date("d", $dateParts), $data['recordingfile']);
            $data['recordingfile'] = $recordingFilePath;
            $export[] = $data;
        }

        $this->sendSuccess($export);
    }


    private function getChannels()
    {
        $output = [];
        exec("asterisk -rx 'core show channels'", $output);

        if (count($output) > 4) {
            $processedOutput = array_slice($output, 1, count($output) - 4);
        } else {
            $processedOutput = [];
        }

        $export = [];

        foreach ($processedOutput as $line) {
            if (preg_match('/(SIP\/(\d+)-\w+)\s+s@macro-dialout-trun\s+\w+\s+Dial\(SIP\/MMT-Out\/(\d+)/', $line, $matches)) {
                $export[] = [
                    'channel' => $matches[1],
                    'extension' => $matches[2],
                    'destination' => $matches[3],
                    'status' => 'up'
                ];
            } elseif (preg_match('/(SIP\/(\d+)-\w+)\s+s-BUSY@macro-dialout/', $line, $matches)) {
                $export[] = [
                    'channel' => $matches[1],
                    'extension' => $matches[2],
                    'destination' => '-',
                    'status' => 'down'
                ];
            } elseif (preg_match('/SIP\/MMT-Out-\w+\s+(\d+)@from-tr\w*\s+Ringing/', $line, $matches)) {
                $export[] = [
                    'channel' => '-',
                    'extension' => '-',
                    'destination' => $matches[1],
                    'status' => 'ring'
                ];
            }
        }

        $this->sendSuccess($export);

    }


    private function getCdrData($f3, $db)
    {
        $startDate = $f3->get('REQUEST.start_date') ?: '2025-01-01';
        $endDate = $f3->get('REQUEST.end_date') ?: '2025-01-15';
        $extension = $f3->get('REQUEST.extension') ?: 'all';

        if (!$this->validateDate($startDate) || !$this->validateDate($endDate)) {
            $this->sendError('Invalid date format. Use YYYY-MM-DD.', 400);
            return;
        }

        if ($extension === "all") {
            $query = $db->exec(
                "SELECT calldate, clid, src, dst, dcontext, channel, dstchannel, disposition, billsec, duration, uniqueid, recordingfile, cnum, cnam FROM asteriskcdrdb.cdr WHERE calldate BETWEEN ? AND ? ORDER BY calldate DESC",
                [$startDate, $endDate]
            );
        } else {
            $query = $db->exec(
                "SELECT calldate, clid, src, dst, dcontext, channel, dstchannel, disposition, billsec, duration, uniqueid, recordingfile, cnum, cnam FROM asteriskcdrdb.cdr WHERE (cnum=? OR cnam=? OR src=?) AND calldate BETWEEN ? AND ? ORDER BY calldate DESC",
                [$extension, $extension, $extension, sprintf("%s 00:00:01",$startDate), sprintf("%s 23:59:59",$endDate)]
            );
        }

        $export = [];
        foreach ($query as $data) {
            $dateParts = strtotime($data['calldate']);
            $recordingFilePath = sprintf("/%s/%s/%s/%s", date("Y", $dateParts), date("m", $dateParts), date("d", $dateParts), $data['recordingfile']);
            $data['recordingfile'] = $recordingFilePath;
            $export[] = $data;
        }

        $this->sendSuccess($export);
    }


    private function getCdrDataSearch($f3, $db)
    {

        $startDate = $f3->get('REQUEST.start_date');
        $endDate = $f3->get('REQUEST.end_date');

        $extension = $f3->get('REQUEST.extension');
        $calledNumber = $f3->get('REQUEST.called_number');

        // Get pagination parameters
        $page = (int)$f3->get('REQUEST.page') ?: 1;
        $perPage = 20;

        if (!$startDate || !$endDate || !$this->validateDate($startDate) || !$this->validateDate($endDate)) {
            $this->sendError('Invalid date format or missing date parameters. Use YYYY-MM-DD.', 400);
            return;
        }

        $conditions = "calldate BETWEEN ? AND ?";
        $params = [sprintf("%s 00:00:01", $startDate), sprintf("%s 23:59:59", $endDate)];

        if ($extension && $extension !== "all") {
            $conditions .= " AND (cnum=? OR cnam=? OR src=?)";
            $params = array_merge($params, [$extension, $extension, $extension]);
        }

        if ($calledNumber) {
            $conditions .= " AND (dst=?)";
            $params[] = $calledNumber;
        }

        $countSql = "SELECT COUNT(*) as total FROM asteriskcdrdb.cdr WHERE " . $conditions;
        $totalRecords = $db->exec($countSql, $params)[0]['total'];

        $totalPages = ceil($totalRecords / $perPage);

        if ($page < 1) $page = 1;
        if ($page > $totalPages && $totalPages > 0) $page = $totalPages;

        $offset = ($page - 1) * $perPage;

        $sql = "SELECT * FROM asteriskcdrdb.cdr WHERE " . $conditions . " ORDER BY calldate DESC LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;

        $query = $db->exec($sql, $params);

        $data = [];
        foreach ($query as $item) {
            $dateParts = strtotime($item['calldate']);
            $recordingFilePath = sprintf("/%s/%s/%s/%s",
                date("Y", $dateParts),
                date("m", $dateParts),
                date("d", $dateParts),
                $item['recordingfile']
            );
            $item['recordingfile'] = $recordingFilePath;
            $data[] = $item;
        }

        $baseUrl = $f3->get('PATH');
        $queryParams = $f3->get('GET');


        if ($page > 1) {
            $prevQueryParams = $queryParams;
            $prevQueryParams['page'] = $page - 1;
            $prevPageUrl = $baseUrl . '?' . http_build_query($prevQueryParams);
        } else {
            $prevPageUrl = null;
        }

        if ($page < $totalPages) {
            $nextQueryParams = $queryParams;
            $nextQueryParams['page'] = $page + 1;
            $nextPageUrl = $baseUrl . '?' . http_build_query($nextQueryParams);
        } else {
            $nextPageUrl = null;
        }

        $firstQueryParams = $queryParams;
        $firstQueryParams['page'] = 1;
        $firstPageUrl = $baseUrl . '?' . http_build_query($firstQueryParams);

        $lastQueryParams = $queryParams;
        $lastQueryParams['page'] = $totalPages;
        $lastPageUrl = $baseUrl . '?' . http_build_query($lastQueryParams);

        $from = $totalRecords ? ($offset + 1) : 0;
        $to = min($offset + $perPage, $totalRecords);

        $response = [
            'current_page' => $page,
            'data' => $data,
            'first_page_url' => $firstPageUrl,
            'from' => $from,
            'last_page' => $totalPages,
            'last_page_url' => $lastPageUrl,
            'next_page_url' => $nextPageUrl,
            'path' => $baseUrl,
            'per_page' => $perPage,
            'prev_page_url' => $prevPageUrl,
            'to' => $to,
            'total' => $totalRecords
        ];

        $this->sendSuccess($response);
    }

    private function getCDRMonitor($f3, $db)
    {
        $startDate = $f3->get('REQUEST.start_date') ?: date("Y-m-d 00:00:01",strtotime("-2 days"));
        $endDate = $f3->get('REQUEST.end_date') ?: date("Y-m-d 23:59:01");
        $extension = $f3->get('REQUEST.extension') ?: 'all';

        if (!$this->validateDate($startDate) || !$this->validateDate($endDate)) {
            $this->sendError('Invalid date format. Use YYYY-MM-DD.', 400);
            return;
        }

        // Her zaman tüm disposition'ları grupla
        if ($extension === 'all') {
            $query = $db->prepare("
            SELECT
                disposition,
                COUNT(*) AS total_calls,
                SUM(duration) AS total_seconds,
                SUM(duration)/60 AS total_minutes
            FROM asteriskcdrdb.cdr
            WHERE calldate BETWEEN ? AND ?
            GROUP BY disposition
        ");
            $query->execute([sprintf("%s 00:00:01",$startDate), sprintf("%s 23:59:59",$endDate)]);
        } else {
            // Belirli extension için tüm disposition'ları grupla
            $query = $db->prepare("
            SELECT
                disposition,
                COUNT(*) AS total_calls,
                SUM(duration) AS total_seconds,
                SUM(duration)/60 AS total_minutes
            FROM asteriskcdrdb.cdr
            WHERE (cnum=? OR cnam=? OR src=?)
            AND calldate BETWEEN ? AND ?
            GROUP BY disposition
        ");
            $query->execute([$extension, $extension, $extension, sprintf("%s 00:00:01",$startDate), sprintf("%s 23:59:59",$endDate)]);
        }

        $results = $query->fetchAll(PDO::FETCH_ASSOC);

        if (empty($results)) {
            $results = [
                [
                    'disposition' => 'NO DATA',
                    'total_calls' => 0,
                    'total_seconds' => 0,
                    'total_minutes' => 0
                ]
            ];
        } else {
            // Toplam sonuçları da ekle
            $grandTotal = [
                'disposition' => 'TOTAL',
                'total_calls' => 0,
                'total_seconds' => 0,
                'total_minutes' => 0
            ];

            foreach ($results as $result) {
                $grandTotal['total_calls'] += $result['total_calls'];
                $grandTotal['total_seconds'] += $result['total_seconds'];
                $grandTotal['total_minutes'] += $result['total_minutes'];
            }

            $results[] = $grandTotal;
        }

        $this->sendSuccess($results);
    }


    private function getPlayerData($f3)
    {
        $file = $f3->get('REQUEST.file');

        if (empty($file)) {
            $this->sendError('File parameter is required for player action.', 400);
            return;
        }


        if(file_exists("/var/spool/asterisk/monitor$file")){

            $filePath = "/var/spool/asterisk/monitor$file";

            header('Content-Type: audio/mpeg');
            header('Content-Length: ' . filesize($filePath));
            header(sprintf('Content-Disposition: inline; filename="%s"', $file));

            readfile($filePath);

        }else if(file_exists("/var/spool/asterisk/monitor$file.mp3")){

            $filePath = "/var/spool/asterisk/monitor$file.mp3";

            header('Content-Type: audio/mpeg');
            header('Content-Length: ' . filesize($filePath));
            header(sprintf('Content-Disposition: inline; filename="%s"', $file));

            readfile($filePath);

        }else{
            echo 'File not found.';
        }

    }


    private function validateDate($date, $format = 'Y-m-d')
    {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }

    private function sendSuccess($data)
    {
        header('Content-type: application/json');
        echo json_encode(['status' => 'success', 'data' => $data]);
    }

    private function sendError($message, $code = 500)
    {
        header('Content-type: application/json');
        http_response_code($code);
        echo json_encode(['status' => 'error', 'message' => $message]);
    }
}

