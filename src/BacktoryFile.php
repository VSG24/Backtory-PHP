<?php
namespace VSG24\Backtory;

class BacktoryFile
{
    private $BASE_REQUESTS_ADDR;
    private $client;

    public function __construct()
    {
        $this->BASE_REQUESTS_ADDR = Backtory::$BASE_REQUESTS_ADDR . "://storage.backtory.com/";
        $clientOptions = [
            'base_uri' => $this->BASE_REQUESTS_ADDR,
        ];
        if(Backtory::$proxyUrl != '') {
            $clientOptions['defaults'] = [
                "allow_redirects" => true, "exceptions" => true,
                'decode_content' => true
            ];
            $clientOptions['cookies'] = true;
            $clientOptions['verify'] = false;
            $clientOptions['proxy'] = Backtory::$proxyUrl;
        }
        $this->client = new \GuzzleHttp\Client($clientOptions);
    }

    public function createNewFolder($path, \Closure $handler)
    {
        $url = 'directories';

        $headers = [];
        $headers[Backtory::$BacktoryStorageIdHeaderKey] = Backtory::$BacktoryStorageId;
        $headers['Authorization'] = 'Bearer ' . Backtory::$BacktoryAuthorizationToken;
        $headers['Content-Type'] = 'application/json';

        $options = [];
        $options['http_errors'] = Backtory::$httpErrors;
        $options['headers'] = $headers;

        $options['json'] = ['path' => $path];

        $res = $this->client->request('POST', $url, $options);
        $code = $res->getStatusCode();

        if($code != 200 && $code != 201) {
            switch ($code)
            {
                case 500:
                    $handler(new ResponseError("Internal Server error. Make sure input has JSON encoding and provides all the required parameters.", null));
                    break;
                case 503:
                    $handler(new ResponseError("Requested service is disabled.", null));
                    break;
                case 400:
                    $handler(new ResponseError("Request has syntax issues.", null));
                    break;
                case 401:
                    $handler(new ResponseError("Unauthorized access. Make sure Authorization header is set.", null));
                    break;
                case 403:
                    $handler(new ResponseError('Access denied; Make sure Backtory-Storage-Id header is provided.', null));
                    break;
                case 404:
                    $handler(new ResponseError("Backtory-Storage-Id incorrect or nonexistent.", null));
                    break;
                default:
                    $handler(new ResponseError("Unknown Error", null));
                    break;
            }
            return;
        }

        $handler(null);
    }

    public function deleteFileOrFolder(array $filesAndFolders, \Closure $handler)
    {
        $url = 'files/delete';

        $headers = [];
        $headers[Backtory::$BacktoryStorageIdHeaderKey] = Backtory::$BacktoryStorageId;
        $headers['Authorization'] = 'Bearer ' . Backtory::$BacktoryAuthorizationToken;
        $headers['Content-Type'] = 'application/json';

        $options = [];
        $options['http_errors'] = Backtory::$httpErrors;
        $options['headers'] = $headers;

        $options['json'] = [
            'urls' => [],
            'forced' => []
        ];

        foreach ($filesAndFolders as $item)
        {
            $options['json']['urls'][] = $item['url'];
            $options['json']['forced'][] = $item['forced'];
        }

        $res = $this->client->request('POST', $url, $options);
        $code = $res->getStatusCode();

        if($code != 200) {
            switch ($code)
            {
                case 500:
                    $handler(new ResponseError("Internal Server error. Make sure input has JSON encoding and provides all the required parameters.", null));
                    break;
                case 503:
                    $handler(new ResponseError("Requested service is disabled.", null));
                    break;
                case 400:
                    $handler(new ResponseError("Request has syntax issues.", null));
                    break;
                case 401:
                    $handler(new ResponseError("Unauthorized access. Make sure Authorization header is set.", null));
                    break;
                case 403:
                    $handler(new ResponseError('Access denied; Make sure Backtory-Storage-Id header is provided.', null));
                    break;
                case 404:
                    $handler(new ResponseError("Backtory-Storage-Id incorrect or nonexistent or files directories do not exist.", null));
                    break;
                case 417:
                    $handler(new ResponseError("Expectation failed. More than 1000 files listed or incorrect paths provided.", null));
                    break;
                default:
                    $handler(new ResponseError("Unknown Error", null));
                    break;
            }
            return;
        }

        $handler(null);
    }

    public function uploadFiles(array $files, \Closure $handler)
    {
        $url = 'files';

        $headers = [];
        $headers[Backtory::$BacktoryStorageIdHeaderKey] = Backtory::$BacktoryStorageId;
        $headers['Authorization'] = 'Bearer ' . Backtory::$BacktoryAuthorizationToken;

        $options = [];
        $options['http_errors'] = Backtory::$httpErrors;
        $options['headers'] = $headers;

        $multipartParams = [];
        foreach ($files as $i => $data)
        {
            $multipartParams[] = [
                'name' => "fileItems[{$i}].path",
                'contents' => $data['path']
            ];
            $multipartParams[] = [
                'name' => "fileItems[{$i}].fileToUpload",
                'contents' => $data['fileToUpload']
            ];
            $multipartParams[] = [
                'name' => "fileItems[{$i}].replacing",
                'contents' => $data['replacing']
            ];
        }

        $options['multipart'] = $multipartParams;

        $res = $this->client->request('POST', $url, $options);
        $code = $res->getStatusCode();

        echo $res->getBody();

        if($code != 200 && $code != 201) {
            switch ($code)
            {
                case 500:
                    $handler(new ResponseError("Internal Server error. Make sure input has JSON encoding and provides all the required parameters.", null));
                    break;
                case 503:
                    $handler(new ResponseError("Requested service is disabled.", null));
                    break;
                case 400:
                    $handler(new ResponseError("Request has syntax issues.", null));
                    break;
                case 401:
                    $handler(new ResponseError("Unauthorized access. Make sure Authorization header is set.", null));
                    break;
                case 403:
                    $handler(new ResponseError('Access denied; Make sure Backtory-Storage-Id header is provided.', null));
                    break;
                case 404:
                    $handler(new ResponseError("Backtory-Storage-Id incorrect or nonexistent.", null));
                    break;
                case 417:
                    $handler(new ResponseError("A file with the same name already exists. To force replace the file you have to set the replacing value to true.", null));
                    break;
                default:
                    $handler(new ResponseError("Unknown Error", null));
                    break;
            }
            return;
        }

        $handler(null);
    }

    function renameFiles(array $filesOldAndNewNames, \Closure $handler)
    {
        $url = 'files/rename';

        $headers = [];
        $headers[Backtory::$BacktoryStorageIdHeaderKey] = Backtory::$BacktoryStorageId;
        $headers['Authorization'] = 'Bearer ' . Backtory::$BacktoryAuthorizationToken;
        $headers['Content-Type'] = 'application/json';

        $options = [];
        $options['http_errors'] = Backtory::$httpErrors;
        $options['headers'] = $headers;

        $options['json'] = ['items' => $filesOldAndNewNames];

        $res = $this->client->request('POST', $url, $options);
        $code = $res->getStatusCode();

        echo $res->getBody();

        if($code != 200) {
            switch ($code)
            {
                case 500:
                    $handler(new ResponseError("Internal Server error. Make sure input has JSON encoding and provides all the required parameters.", null));
                    break;
                case 503:
                    $handler(new ResponseError("Requested service is disabled.", null));
                    break;
                case 400:
                    $handler(new ResponseError("Request has syntax issues.", null));
                    break;
                case 401:
                    $handler(new ResponseError("Unauthorized access. Make sure Authorization header is set.", null));
                    break;
                case 403:
                    $handler(new ResponseError('Access denied; Make sure Backtory-Storage-Id header is provided.', null));
                    break;
                case 404:
                    $handler(new ResponseError("Backtory-Storage-Id incorrect or nonexistent.", null));
                    break;
                case 417:
                    $handler(new ResponseError("A file with the same name already exists. To force replace the file you have to set the replacing value to true.", null));
                    break;
                default:
                    $handler(new ResponseError("Unknown Error", null));
                    break;
            }
            return;
        }

        $handler(null);
    }

    function getDirectoryInfo($urlForInfo, $pageNumber, $pageSize, $sortingType, \Closure $handler)
    {
        $url = 'files/directoryInfo';

        $headers = [];
        $headers[Backtory::$BacktoryStorageIdHeaderKey] = Backtory::$BacktoryStorageId;
        $headers['Authorization'] = 'Bearer ' . Backtory::$BacktoryAuthorizationToken;
        $headers['Content-Type'] = 'application/json';

        $options = [];
        $options['http_errors'] = Backtory::$httpErrors;
        $options['headers'] = $headers;

        $options['json'] = [
            'url' => $urlForInfo,
            'pageNumber' => $pageNumber,
            'pageSize' => $pageSize,
            'sortingType' => $sortingType
        ];

        $res = $this->client->request('POST', $url, $options);
        $code = $res->getStatusCode();

        echo $res->getBody();

        if($code != 200) {
            switch ($code)
            {
                case 500:
                    $handler(new ResponseError("Internal Server error. Make sure input has JSON encoding and provides all the required parameters.", null));
                    break;
                case 503:
                    $handler(new ResponseError("Requested service is disabled.", null));
                    break;
                case 400:
                    $handler(new ResponseError("Request has syntax issues.", null));
                    break;
                case 401:
                    $handler(new ResponseError("Unauthorized access. Make sure Authorization header is set.", null));
                    break;
                case 403:
                    $handler(new ResponseError('Access denied; Make sure Backtory-Storage-Id header is provided.', null));
                    break;
                case 404:
                    $handler(new ResponseError("Backtory-Storage-Id incorrect or nonexistent.", null));
                    break;
                case 417:
                    $handler(new ResponseError("A file with the same name already exists. To force replace the file you have to set the replacing value to true.", null));
                    break;
                default:
                    $handler(new ResponseError("Unknown Error", null));
                    break;
            }
            return;
        }

        $handler(null);
    }

    function copyFilesAndFolders(array $filesAndFolders, $destinationPath, \Closure $handler)
    {
        $url = 'files/copy';

        $headers = [];
        $headers[Backtory::$BacktoryStorageIdHeaderKey] = Backtory::$BacktoryStorageId;
        $headers['Authorization'] = 'Bearer ' . Backtory::$BacktoryAuthorizationToken;

        $options = [];
        $options['http_errors'] = Backtory::$httpErrors;
        $options['headers'] = $headers;

        $options['json'] = [
            'sourceUrls' => [],
            'forces' => [],
            'destinationUrl' => $destinationPath
        ];

        foreach ($filesAndFolders as $item)
        {
            $options['json']['sourceUrls'][] = $item['url'];
            $options['json']['forces'][] = $item['forced'];
        }

        $res = $this->client->request('POST', $url, $options);
        $code = $res->getStatusCode();

        echo $res->getBody();

        if($code != 200 && $code != 201) {
            switch ($code)
            {
                case 500:
                    $handler(new ResponseError("Internal Server error. Make sure input has JSON encoding and provides all the required parameters.", null));
                    break;
                case 503:
                    $handler(new ResponseError("Requested service is disabled.", null));
                    break;
                case 400:
                    $handler(new ResponseError("Request has syntax issues.", null));
                    break;
                case 401:
                    $handler(new ResponseError("Unauthorized access. Master access required. Make sure Authorization header is set.", null));
                    break;
                case 403:
                    $handler(new ResponseError('Access denied; Make sure Backtory-Storage-Id header is provided.', null));
                    break;
                case 404:
                    $handler(new ResponseError("Files or folders not found or Backtory-Storage-Id incorrect or nonexistent.", null));
                    break;
                case 417:
                    $handler(new ResponseError("More than 100 files or files do not have the same parent. A file with the same name already exists. To force replace the file you have to set the replacing value to true.", null));
                    break;
                default:
                    $handler(new ResponseError("Unknown Error", null));
                    break;
            }
            return;
        }

        $handler(null);
    }

    function moveFilesAndFolders(array $filesAndFolders, $destinationPath, \Closure $handler)
    {
        $url = 'files/move';

        $headers = [];
        $headers[Backtory::$BacktoryStorageIdHeaderKey] = Backtory::$BacktoryStorageId;
        $headers['Authorization'] = 'Bearer ' . Backtory::$BacktoryAuthorizationToken;

        $options = [];
        $options['http_errors'] = Backtory::$httpErrors;
        $options['headers'] = $headers;

        $options['json'] = [
            'sourceUrls' => [],
            'forces' => [],
            'destinationUrl' => $destinationPath
        ];

        foreach ($filesAndFolders as $item)
        {
            $options['json']['sourceUrls'][] = $item['url'];
            $options['json']['forces'][] = $item['forced'];
        }

        $res = $this->client->request('POST', $url, $options);
        $code = $res->getStatusCode();

        echo $res->getBody();

        if($code != 200) {
            switch ($code)
            {
                case 500:
                    $handler(new ResponseError("Internal Server error. Make sure input has JSON encoding and provides all the required parameters.", null));
                    break;
                case 503:
                    $handler(new ResponseError("Requested service is disabled.", null));
                    break;
                case 400:
                    $handler(new ResponseError("Request has syntax issues.", null));
                    break;
                case 401:
                    $handler(new ResponseError("Unauthorized access. Master access required. Make sure Authorization header is set.", null));
                    break;
                case 403:
                    $handler(new ResponseError('Access denied; Make sure Backtory-Storage-Id header is provided.', null));
                    break;
                case 404:
                    $handler(new ResponseError("Files or folders not found or Backtory-Storage-Id incorrect or nonexistent.", null));
                    break;
                case 417:
                    $handler(new ResponseError("More than 100 files or files do not have the same parent. A file with the same name already exists. To force replace the file you have to set the replacing value to true.", null));
                    break;
                default:
                    $handler(new ResponseError("Unknown Error", null));
                    break;
            }
            return;
        }

        $handler(null);
    }
}