<?php
error_reporting(E_ERROR | E_PARSE);
$src = json_encode($_POST['src']);

echo json_encode(output_obj(first_pass($src)));
function first_pass($src) {
	// $src = file_get_contents($filename);
	$lines = explode('\n', $src);
	// echo json_encode($lines[0]);
	$lc = 0x0;
	$lcctr = "";
	$output = array();
	$label_addresses = array();
	foreach ($lines as $line) {

		if (strpos($line, ',')  !== false) {
			$output[] = array($line[0] . ' ' . $line[1], charhex($line[0]) . ' ' . charhex($line[1]),
								get_bin(charhex($line[0]) . charhex($line[1])));
			$output[] = array($line[2] . ' ' . $line[3], charhex($line[2]) . ' ' . charhex($line[3]),
								get_bin(charhex($line[2]) . charhex($line[3])));
			$lcctr = (string)dechex(($lc));

			$label = explode(',', $line)[0];
			$label_addresses[] = array($label, strtoupper($lcctr));
			
			if ($lc <= 0xFFF) {
				$output[] = array('0' . $lcctr[0] . ' ' . $lcctr[1] . $lcctr[2],
								get_bin('0' . $lcctr)); 
			} else {
				$output[] = array($lcctr[0] . $lcctr[1] . ' ' . $lcctr[2] . $lcctr[3],
									get_bin($lcctr)); 
			}
		} elseif (strpos($line, 'ORG')  !== false) {
			$lc = hexdec(explode(' ', $line)[1]) - 1;
			$lcctr = (string) hexdec($lc);
		} elseif (strpos($line, 'END')  !== false) {
			break;
		}
		$lc++;
	}

	return second_pass($src, array("output" => $output, "label_addresses" => $label_addresses));
}

function second_pass($src, $first_pass_result) {
	$label_addresses = $first_pass_result["label_addresses"];
	$lines = explode('\n', $src);
	$lc = 0x0;
	$output = array();
	foreach ($lines as $line) {
		$address = "000000000000";
		$opcode = "000";
		$first_bit = "0";
		if (strpos($line, "ORG") !== false) {
			$lc = hexdec(explode(' ', $line)[1]) - 1;
			continue;
		} else {
			if (strpos($line, "END") !== false) {
				//do end stuff here like return?
				return array("output"=> $output, "first_pass_result"=>$first_pass_result);
			} else {
				if (strpos($line, "DEC") !== false) {
					$opcode = "";
					if (strpos($line, ",") !== false) {
						$val = explode(' ', $line)[2];
					} else {
						$val = explode(' ', $line)[1];
					}
					if ($val >= 0) {
						$address = decbin($val);
					} else {
						$first_bit = "1";
						$address = substr(decbin($val), -15);
					}
					$lc++;
					$output[] = array(dechex_($lc), str_pad($first_bit . $opcode . str_pad($address, 12, '0', STR_PAD_LEFT), 16, '0', STR_PAD_LEFT));
					continue;
				} elseif (strpos($line, "HEX") !== false) {
					$opcode = "";
					if (strpos($line, ",") !== false) {
						$val = explode(' ', $line)[2];
					} else {
						$val = explode(' ', $line)[1];
					}
					if ($val >= 0) {
						$address = hexbin($val);
					} else {
						$first_bit = "1";
						$address = substr(hexbin($val), -15);
					}
					$lc++;
					$output[] = array(dechex_($lc), str_pad($first_bit . $opcode . str_pad($address, 12, '0', STR_PAD_LEFT), 16, '0', STR_PAD_LEFT));
					continue;
				}
			}
		}

		if (strpos($line, "AND") !== false or strpos($line, "ADD") !== false or 
					strpos($line, "LDA") !== false or strpos($line, "STA") !== false or 
					strpos($line, "BUN") !== false or strpos($line, "BSA") !== false or 
					strpos($line, "ISZ") !== false  ) {
			$val = explode(' ', $line);
			if (strpos($line, ",") !== false) {
				$label = $val[0];
				$inst = $val[1];
				$operand = $val[2];
			} else {
				$inst = $val[0];
				$operand = $val[1];
			}
			$opcode = str_pad(get_machine_code($inst), 3, '0', STR_PAD_LEFT);
			foreach ($label_addresses as $label_address) {
				if (strcmp($label_address[0], $operand) == 0) {
					$address = hexbin($label_address[1]);
					continue;
				}
			}

			if ((strpos($line, ",") !== false or (isset($val[2]) and $val[2] == 'I')) or 
				(strpos($line, ",") === false or (isset($val[3]) and $val[3] == 'I'))) {
				$first_bit = "1";
			} else {
				$first_bit = "0";
			}
			$lc++;
			$output[] = array(dechex_($lc), str_pad($first_bit . $opcode . str_pad($address, 12, '0', STR_PAD_LEFT), 16, '0', STR_PAD_LEFT));
			continue;//make output
		} else {
			if (strpos($line, "HLT") !== false or strpos($line, "SZE") !== false or 
					strpos($line, "SZA") !== false or strpos($line, "SNA") !== false or 
					strpos($line, "SPA") !== false or strpos($line, "INC") !== false or 
					strpos($line, "CIL") !== false or strpos($line, "CIR") !== false or 
					strpos($line, "CME") !== false or strpos($line, "CMA") !== false or 
					strpos($line, "CLE") !== false or strpos($line, "CLA") !== false  ) {
				if (strpos($line, ",")) {
					$val = trim(explode(',', $line)[1]);
				} else {
					$val = $line;
				}
				$opcode = "";
				$address = get_machine_code($val);
			} else {
				if (strpos($line, ",")) {
					$val = trim(explode(',', $line)[1]);
				} else {
					$val = $line;
				}
				$first_bit = "1";
				$opcode = "";
				$address = get_machine_code($val);
			}
			$lc++;
			$output[] = array(dechex_($lc), str_pad($first_bit . $opcode . str_pad($address, 12, '0', STR_PAD_LEFT), 16, '0', STR_PAD_LEFT));
			continue;
		}
	}
	return array("output"=> $output, "first_pass_result"=>$first_pass_result);
}


function charhex($char) {
	return strtoupper(dechex(ord($char)));
}

function dechex_($dec) {
	return strtoupper(dechex($dec));
}

function charbin($char) {
	return decbin(ord($char));
}

function hexbin($hex) {
	if ($hex < 0) {
		return decbin(hexdec($hex) * -1);
	}
	return decbin(hexdec($hex));
}

function get_machine_code($symbol) {
	$instruction = array(
	"CLA" => "7800", "CLE" => "7400", "CMA" => "7200", "CME" => "7100",
	"CIR" => "7080", "CIL" => "7040", "INC" => "7020", "SPA" => "7010",
	"SNA" => "7008", "SZA" => "7004", "SZE" => "7002", "HLT" => "7001",
	"INP" => "F800", "OUT" => "F400", "SKI" => "F200", "SKO" => "F100",
	"ION" => "F080", "IOF" => "F040",
	"AND" => "0", "ADD" => "1", "LDA" => "2", "STA" => "3", 
	"BUN" => "4", "BSA" => "5", "ISZ" => "6"
	);
	return hexbin($instruction[$symbol]);
}

function encode($num) {
	$res = "";
	for ($i=0; $i < 12; $i++) { 
		if($i == $num) {
			$res .= "1";
		} else {
			$res .= "0";
		}
	}
	return strrev($res);
}

function get_addr($loc){
	$loc = (string)$loc;
	$res = "";
	for ($i=0; $i < strlen($loc); $i++) { 
		$res .= str_pad(hexbin($loc[$i]), 4, STR_PAD_LEFT);
	}

	$res = str_pad($res, 12, STR_PAD_LEFT);
	return $res;
}

function get_bin($hex_str) {
	$res = "";
	for ($i=0; $i < strlen($hex_str); $i++) { 
		$res .= str_pad(hexbin($hex_str[$i]), 4, '0', STR_PAD_LEFT) . ' ';
	}
	return trim($res);
}

// Convert a binary expression (e.g., "100111") into a binary-string
function bin2bstr($input)
{
    if (!is_string($input)) return null; // Sanity check

    // Pack into a string
    return pack('H*', base_convert($input, 2, 16));
}

function output_obj($output_second_pass){
    $str = '';
    $output = $output_second_pass["output"];
    foreach ($output as $out) {
        $str .= bin2bstr($out[1]);
    }
    $f = fopen("out/output.obj", "wb");
    fwrite($f, $str);
    fclose($f);
    return $output_second_pass;
}

?>