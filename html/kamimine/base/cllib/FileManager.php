<?php

require_once (APP.'base/cllib/Util.php');

/**
 * ファイル管理クラス
 * CSVファイル/ZIPファイルの生成や、ダウンロード機能を提供する。
 */
class FileManager {

    private $logger;
    private $file_arr;
    private $tmp_dir;

    function __construct($logger=null) {
        $this->logger = $logger;
        $this->file_arr = array();
        $this->tmp_dir = uniqid(date("YmdHis")."-");
    }

    /**
     * CSVファイル生成
     * header/dataの2つの配列よりCSVファイルを生成。ダウンロードまでは行わない。生成のみ。
     * $dir/$filenameは任意パラメータ。意図してサーバ上にファイル保存する場合を除き、指定するべきではない。
     * ※複数リクエスト発生時のバッティング、散在したファイルの削除漏れの発生などを防ぐため。
     * なお、EXCELでの文字化け回避のため、文字コードはUTF-8->SJISに変換して出力している
     * @param $header_arr ヘッダ配列　ex. array('1列目','2列目'...)
     * @param $data_arr データ配列　下記のような二重配列
     *                   [0]=> array(2) { [0]=> "123", [1]=> "456" }
     *                   [1]=> array(2) { [0]=> "ZZ" , [1]=> "XX"  }
     *                           :
     * @param $filename 物理ファイル名。任意。ユーザに表示されるファイル名ではない。
     * @param $dir 出力ディレクトリ。任意。指定しない場合、variables.phpのREPORT_OUTPUT_DIRで指定したディレクトリ。指定する場合は末尾/スラッシュ。
     */
    public function generateCsv($header_arr,$data_arr,$filename=null,$dir=null) {
        // ディレクトリ設定
        if(is_null($dir)) $dir = REPORT_OUTPUT_DIR;
        // ファイル名設定
        if(is_null($filename)) $filename = uniqid(date("YmdHis")."-").".csv";
        // ファイルポインタをオープン
        $file = fopen($dir.$filename, "w");
        // データ書き込み 
        if($file) {
            // ヘッダ書き込み 
            //if(!is_null($header_arr)) var_dump(fputcsv($file, $header_arr));
            if(!is_null($header_arr)) {
                mb_convert_variables("SJIS","UTF-8",$header_arr);
                fputcsv($file, $header_arr);
            }
            // データ書き込み
            foreach($data_arr as $i => $row) {
                //var_dump(fputcsv($file, $row));
                mb_convert_variables("SJIS","UTF-8",$row);
                fputcsv($file, $row);
            }
        }
        // ファイルポインタをクローズ
        fclose($file);
        // ファイルフルパスを返却
        return $dir.$filename;
    }

    /**
     * ZIP対象ファイル登録
     * 本メソッドでZIP対象ファイルを登録した後、generateZipでZIPファイルを生成する。
     * @param $file ファイル物理名（パス付き）　ex./tmp/output/20151014171733-561e0f9da01fb.csv
     * @param $filename ファイル名（ユーザに見せるファイル名） ex.サンプルCSV
     * @param $dir ZIPファイル内部のフォルダ　※指定しない場合は直下に格納される。現時点1階層のみを想定多階層には対応していない。
     */
    public function addZipfile($file,$filename,$dir=null) {
        // ディレクトリ指定がない場合（ZIP直下にファイルを置く場合）は、仮のIDを付けておく
        if(is_null($dir)) $dir = $this->tmp_dir;
        // 格納
        $this->file_arr[$dir] = array_merge((array)$this->file_arr[$dir],array($filename => $file));
    }

    /**
     * ZIPファイル生成
     * ダウンロードまでは行わない。生成のみ。ZIP元ファイルは削除される。
     * $dir/$filenameは任意パラメータ。意図してサーバ上にファイル保存する場合を除き、指定するべきではない。
     * ※複数リクエスト発生時のバッティング、散在したファイルの削除漏れの発生などを防ぐため。
     * @param $filename 物理ファイル名（ユーザに表示されるファイル名ではない）
     * @param $dir 出力ディレクトリ。指定しない場合、variables.phpのREPORT_OUTPUT_DIRで指定したディレクトリ。指定する場合は末尾/スラッシュ。
     */
    public function generateZip($filename=null,$dir=null) {
        $zip = new ZipArchive();
        // ディレクトリ設定
        if(is_null($dir)) $dir = REPORT_OUTPUT_DIR;
        // ZIPファイル名設定
        if(is_null($filename)) $filename = uniqid(date("YmdHis")."-").".zip";
        $zipFile = $dir.$filename;
        // ZIPファイルオープン
        $flg = $zip->open($zipFile, ZipArchive::CREATE);
        if ($flg) {
            // フォルダ
            foreach ($this->file_arr as $dirName => $files) {
                // 仮でなければフォルダ作成
                if($dirName == $this->tmp_dir) {
                    $inDirName = null;
                } else {
                    $inDirName = mb_convert_encoding($dirName,"sjis-win","UTF-8")."/";
                    $zip->addEmptyDir($inDirName);
                }
                // ファイル登録
                foreach($files as $filename => $file) {
                    $zip->addFile($file, $inDirName.mb_convert_encoding($filename,"sjis-win","UTF-8"));
                }
            }
            // ZIPファイルクローズ
            $zip->close();
            // 元ファイル削除
            foreach ($this->file_arr as $dirName => $files) {
                foreach($files as $filename => $file) {
                    if(file_exists($file)) {
                        unlink($file);
                    }
                }
            }
        } else {
            throw new Exception("Zip圧縮ファイルを開けませんでした。ErrCd: ".$flg);
        }
        return $zipFile;
    }

    /**
     * ファイルダウンロード
     * 引数で指定された$fileをHTTPレスポンスに書き込む。
     * 本メソッド呼び出し以降に処理を記述しないこと。
     * @param $file ファイル物理名（パス付き）
     * @param $downloadFileName ダウンロードファイル名（ユーザに表示されるファイル名）　拡張子付きで指定のこと。
     */
    public function download($file,$downloadFileName) {

        // POST通信の場合は処理終了
        if ($_SERVER["REQUEST_METHOD"] == "POST") return;

        // ファイルが指定されているかどうか、また存在しているかどうか
        if (empty($file)) {
            throw new Exception("ダウンロードするファイルを指定してください。");
        } else {
            if (!file_exists($file)) {
                throw new Exception("指定されたファイル(".$file.")が存在しません。");
            } else if (!is_file($file)) {
                throw new Exception("指定されたファイル(".$file.")はファイルではありません。");
            }
        }

        // 文字コード指定
        mb_http_output("UTF-8");
        // ファイル名を設定
        $tmp = explode(".", $downloadFileName);
        $filename = ''; 
        for ($i = 0; $i < count($tmp); $i++) {
            // ドットでつなぎなおす
            if ($i > 0) $filename .= ".";
            $filename .= $tmp[$i];
        }
        // 拡張子からMIME_TYPE指定
        $mimeType = Util::getMimeType($tmp[count($tmp) - 1]);
        header('Content-Type: '.$mimeType);
        // ファイル名指定
        header('Content-Disposition: attachment;filename="'.mb_convert_encoding($filename, "sjis-win", "UTF-8").'"');
        // ファイルサイズ
        header('Content-length: '.filesize($file));
        // キャッシュ設定
        header('Cache-Control: max-age=0');
        // 破損エラー防止
        ob_end_clean();
        // 出力処理
        if ($fp = fopen($file, "r")) {
            while (!feof($fp)) {
                $buffer = fread($fp, 2048);
                echo $buffer;
            }
            fclose($fp);
        } else {
            throw Exception("指定されたファイル(".$file.")を開けませんでした");
        }
        // ファイル削除
        unlink($file);

    }

}
