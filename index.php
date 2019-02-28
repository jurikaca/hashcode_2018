<?php

/**
 * Methods to interact with file, read parameters from file, write output/result on destination file.
 * Class FileReader
 */
class FileReader {
    public $file_name; // current file name

    function __construct($file_name) {
        $this->file_name = $file_name;
    }

    public function readFile()
    {
        $myfile = fopen('task_files/'.$this->file_name, "r") or die("Unable to open file!");

        $output = $this->readFirstLine($myfile); // read the first row from the file

        $output['rides'] = $this->readOtherLines($myfile); // read other lines of file

        fclose($myfile);

        return $output;
    }

    /**
     * read first row from the file
     *
     * @param $myfile, file object
     */
    private function readFirstLine($myfile){
        $first_line = fgets($myfile);
        $first_line = explode(' ', $first_line);

        return [
            'R' => $first_line[0],
            'C' => $first_line[1],
            'F' => $first_line[2],
            'N' => $first_line[3],
            'B' => $first_line[4],
            'T' => $first_line[5],
        ];
    }

    /**
     * read all other lines except first line, collecting data needed for calculation
     *
     * @param $myfile
     */
    private function readOtherLines($myfile){
        $rides = [];
        while(!feof($myfile)) {
            $current_line = fgets($myfile);
            if ($current_line != "") {
                $current_line = explode(' ', $current_line);
                $current_line[count($current_line)-1] = str_replace("\n","",$current_line[count($current_line)-1]);
                $rides[] = new Ride(
                    new Location($current_line[0], $current_line[1]),
                    new Location($current_line[2], $current_line[3]),
                    $current_line[4],
                    $current_line[5]
                );
            }
        }
        return $rides;
    }
}

class HashCode {
    // file names
    const LEVEL_1               = 'level_1.in'; // a_example.in
    const LEVEL_2               = 'level_2.in'; // b_should_be_easy.in
    const LEVEL_3               = 'level_3.in'; // c_no_hurry.in
    const LEVEL_4               = 'level_4.in'; // d_metropolis.in
    const LEVEL_5               = 'level_5.in'; // e_high_bonus.in

    // public $R; // number of rows of the grid (1 ≤ R ≤ 10000)
    // public $C; // number of columns of the grid (1 ≤ C ≤ 10000)
    // public $F; // number of vehicles in the fleet (1 ≤ F ≤ 1000)
    // public $N; // number of rides (1 ≤ N ≤ 10000)
    // public $B; // per-ride bonus for starting the ride on time (1 ≤ B ≤ 10000)
    // public $T; // number of steps in the simulation (1 ≤ T ≤ 10^9 )
    // public $rides; // array of rides details, each ride is an array of 6 values: [[(0, 0), (1, 3), 2, 9], [], []...] => "ride from [0, 0] to [1, 3], earliest start 2, latest finish 9"
    // public $fleet;

    function __construct($file_name) {
        $output = (new FileReader($file_name))->readFile();
        // $this->rides = $output['rides'];
        // $this->R = $output['R'];
        // $this->C = $output['C'];
        // $this->F = $output['F'];
        // $this->N = $output['N'];
        // $this->B = $output['B'];
        // $this->T = $output['T'];
    }


    //////  LOGIC METHODS

    public function init()
    {
        // init variables here
		
		// start simulation
        $this->startSimulation();
    }

    public function startSimulation()
    {
        // start simulation
        
    }


    //////  VIEW/SHOW METHODS

    public function showFirstLineParams() {
        echo $this->R.PHP_EOL;
    }
}

$hash_code = new HashCode(HashCode::LEVEL_1); // new instance passing file name to read

$hash_code->init();
$hash_code->showFirstLineParams();


?>