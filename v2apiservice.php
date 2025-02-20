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
        } elseif ($action === 'channels') {
            $this->getChannels();
        } else {
            $this->sendError('Invalid action.', 400);
        }
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

            $parts = explode(
                ' ',
                preg_replace('/\s+/', ' ', $line),
                4
            );

            if(!strpos($parts[0],"SIP/MMT-Out")){
                $export[] = [
                    "channel" => $parts[0],
                    "extension" => explode("-", explode("/", $parts[0])[1])[0],
                    "location" => $parts[1],
                    "status" => $parts[2],
                    "data" => $parts[0]
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
                [$extension, $extension, $extension, $startDate, $endDate]
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

    private function getCDRMonitor($f3, $db)
    {
        $startDate = $f3->get('REQUEST.start_date') ?: date("Y-m-d 00:00:01",strtotime("-2 days"));
        $endDate = $f3->get('REQUEST.end_date') ?: date("Y-m-d 23:59:01");
        $extension = $f3->get('REQUEST.extension') ?: 'all';
    
        if (!$this->validateDate($startDate) || !$this->validateDate($endDate)) {
            $this->sendError('Invalid date format. Use YYYY-MM-DD.', 400);
            return;
        }
    
        if ($extension === 'all') {
            $query = $db->prepare("
                SELECT 
                    COUNT(*) AS total_calls, 
                    SUM(duration) AS total_seconds, 
                    SUM(duration)/60 AS total_minutes 
                FROM asteriskcdrdb.cdr 
                WHERE calldate BETWEEN ? AND ?
            ");
            $query->execute([$startDate, $endDate]);
        } else {
            $query = $db->prepare("
                SELECT 
                    COUNT(*) AS total_calls, 
                    SUM(duration) AS total_seconds, 
                    SUM(duration)/60 AS total_minutes 
                FROM asteriskcdrdb.cdr 
                WHERE (cnum=? OR cnam=? OR src=?)
                AND calldate BETWEEN ? AND ?
            ");
            $query->execute([$extension, $extension, $extension, $startDate, $endDate]);
        }
    
        $result = $query->fetch(PDO::FETCH_ASSOC);
    
        if (!$result) {
            $result = [
                'total_calls' => 0,
                'total_seconds' => 0,
                'total_minutes' => 0
            ];
        }
    
        $this->sendSuccess($result);
    }
    

    private function getPlayerData($f3)
    {
        $file = $f3->get('REQUEST.file');

        if (empty($file)) {
            $this->sendError('File parameter is required for player action.', 400);
            return;
        }

        $filePath = "/var/spool/asterisk/monitor$file";

        if (file_exists($filePath)) {
            header('Content-Type: audio/mpeg');
            header('Content-Length: ' . filesize($filePath));
            header(sprintf('Content-Disposition: inline; filename="%s"', $file));

            readfile($filePath);
        } else {
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

