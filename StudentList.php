<?php
	
	switch(isset($_GET['is'])? $_GET['is'] : ''){
	case 'sil' : echo pageHeader("Student List"); sil($_GET['no']); listele($_GET['page'], $_GET['column'], $_GET['ascending'], getFindArray()); break;
	case 'ekle' : echo pageHeader("Student List"); ekle($_GET['name'], $_GET['surname'], $_GET['department']); listele($_GET['page'], $_GET['column'], $_GET['ascending'], getFindArray()); break; 
	case 'degistir': echo pageHeader("Update Information"); form($_GET['no'], $_GET['name'], $_GET['surname'], $_GET['department'], $_GET['page'], $_GET['column'], $_GET['ascending']); break;
	case 'guncelle': echo pageHeader("Student List"); guncelle($_GET['no'], $_GET['name'], $_GET['surname'], $_GET['department']); listele($_GET['page'], $_GET['column'], $_GET['ascending'], getFindArray()); break;
	case 'bul': echo pageHeader("Student List"); listele(1, $_GET['column'], $_GET['ascending'], getFindArray()); break;
	default: echo pageHeader("Student List"); listele(isset($_GET['page'])? $_GET['page'] : 1, isset($_GET['column'])? $_GET['column'] : 'no', isset($_GET['ascending'])? $_GET['ascending'] : true, getFindArray());
	}

	function pageHeader($title){ ?>
		<!DOCTYPE html>
		<html lang="en">
			<head>
				<title><?php echo $title; ?></title>
				<meta charset="UTF-8">
				<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
				<style type="text/css">
					.center{
						text-align: center;
					}
					.round-button{
						width: 40px;
						height: 40px;
						margin: 5px 5px;
						padding: 7px 10px;
						border-radius: 20px;
						font-size: 15px;
						text-align: center;
					}
				</style>
			</head>
		</html><?php
	}
	
	function getFindArray(){
		if(isset($_GET['findValues']))
			return array_filter(explode(':', $_GET['findValues']));
		if(isset($_GET['findNo']))
			return array($_GET['findNo'], $_GET['findName'], $_GET['findSurname'], $_GET['findDepartment']);
		return array();
		
	}

	function guncelle($no, $ad, $soyad, $department){
		$connect = connect();
		$query = "SELECT name, surname, department FROM students WHERE no='$no'";
		if(!$retval = mysqli_query($connect, $query)){
			echo "No student found with this number";
			return ;
		}
		$row = mysqli_fetch_array($retval);
		$eskiad = $row['name'];
		$eskisoyad = $row['surname'];
		$eskibolum = $row['department'];
		
		if($ad == $eskiad && $soyad == $eskisoyad && $department == $eskibolum){
			echo "No changes have been made";
			return ;
		}
		$query = "UPDATE students SET ";
		if($ad != $eskiad){
			$query .= "name='$ad'";
			if($soyad != $eskisoyad || $department != $eskibolum)
				$query .= ",";
		}
		if($soyad != $eskisoyad){
			$query .= "surname='$soyad'";
			if($department != $eskibolum)
				$query .= ",";
		}
		if($department != $eskibolum)
			$query .= "department='$department'";
		$query .= " WHERE no='$no'";
		if(!$retval = mysqli_query($connect, $query))
			echo "Update operation failed" . mysqli_error($connect) . $query;
		
		mysqli_close($connect);
	}

	function sil($no){
		$connect = connect();
		$query = "DELETE FROM students WHERE no='$no'";
		if(!$retval = mysqli_query($connect, $query))
			echo "Delete operation failed";
		
		mysqli_close($connect);
	}

	function ekle($ad, $soyad, $department){
		if(empty($ad) && empty($soyad) && empty($department)){
			echo "Please enter information";
			return ;
		}
		$connect = connect();
		
		$query = "INSERT INTO students (name, surname, department) VALUES ('$ad', '$soyad', '$department')";
		if(!$retval = mysqli_query($connect, $query))
			echo "Entry couldn't be added" . mysqli_error($connect);
		
		mysqli_close($connect);
	}

	
	function listele($page, $column, $ascending, $params){
		$ascending = filter_var($ascending, FILTER_VALIDATE_BOOLEAN);
		define("ENTRY_PER_PAGE", 5);
		$connect = connect();
		
		$query = "SELECT AUTO_INCREMENT FROM INFORMATION_SCHEMA.TABLES 
					WHERE TABLE_SCHEMA='university' AND TABLE_NAME='students'";
		$nextNumber = mysqli_fetch_array(mysqli_query($connect, $query))[0];
		
		$updown = ($ascending)? "&nbsp;&nbsp;&nbsp;&nbsp;&#x25b2;" : "&nbsp;&nbsp;&nbsp;&nbsp;&#x25bc;";
		$where = "";
		
		if(!empty($params))
		if($params[0] != "" || $params[1] != "" || $params[2] != "" || $params[3] != ""){
			$no = $params[0];
			$ad = $params[1];
			$soyad = $params[2];
			$department = $params[3];
			
			$where = "WHERE ";
			if($no == "" && $ad == "" && $soyad == "" && $department == ""){
				mysqli_close($connect);
				echo "No parameters have been given";
				return ;
			}
			if($no != ""){
				$where .= "no='$no'";
				if($ad != "" || $soyad != "" || $department != "")
					$where .= " AND ";
			}
			if($ad != ""){
				$where .= "name='$ad'";
				if($soyad != "" || $department != "")
					$where .= " AND ";
			}
			if($soyad != ""){
				$where .= "surname='$soyad'";
				if($department != "")
					$where .= " AND ";
			}
			if($department != "")
				$where .= "department='$department'";	
				
		}
		$query = "SELECT COUNT(no) FROM students " . $where;
		$retval = mysqli_query($connect, $query);
		
		$rowCount = mysqli_fetch_array($retval)[0];
		
		if($rowCount == 0) $pageCount = 1;
		else
			$pageCount = ($rowCount % ENTRY_PER_PAGE == 0)? $rowCount / ENTRY_PER_PAGE : (int)($rowCount / ENTRY_PER_PAGE) + 1;
		//$pageCount = round($rowCount, 5);
	
		
		$query = "SELECT no, name, surname, department FROM students " . $where . " ORDER BY $column "
						. (($ascending)? "ASC" : "DESC") . " LIMIT 5 OFFSET " . ($page - 1) * ENTRY_PER_PAGE;
		//$query = "SELECT no, name, surname, department FROM students ORDER BY $column " . (($ascending)? "ASC" : "DESC") . " LIMIT 5 OFFSET " . ($page - 1) * 5;
		$retval = mysqli_query($connect, $query);
		
		if(!$retval)
			die("Could not retrieve: " . mysqli_error($connect));
		
		echo "<table class='table table-striped' style='table-layout:fixed'>
			<thead class='thead-dark'>
			<tr>
				<th style='width:%10'><a href='StudentList.php?page=" . $page . "&column=no&ascending=" . !$ascending . "&findValues=" . implode(':', getFindArray()) . "'>Student Number</a>" . (($column == 'no')? $updown : '') . "</th>
				<th style='width:%30'><a href='StudentList.php?page=" . $page . "&column=name&ascending=" . !$ascending . "&findValues=" . implode(':', getFindArray()) . "'>Name</a>" . (($column == 'name')? $updown : '') . "</th>
				<th style='width:%30'><a href='StudentList.php?page=" . $page . "&column=surname&ascending=" . !$ascending . "&findValues=" . implode(':', getFindArray()) . "'>Surname</a>" . (($column == 'surname')? $updown : '') . "</th>
				<th style='width:%30'><a href='StudentList.php?page=" . $page . "&column=department&ascending=" . !$ascending . "&findValues=" . implode(':', getFindArray()) . "'>Department</a>" . (($column == 'department')? $updown : '') . "</th>
				<th></th>
				<th></th>
			</tr>
			</thead>
			<tbody>
			<form method='GET'>
				
				<tr style='background: rgba(150, 255, 150, .3)'>
				<td>" . $nextNumber . "</td>
				<td><input type='text' name='name' /></td>
				<td><input type='text' name='surname' /></td>
				<td><input type='text' name='department' /></td>
				<input type='hidden' name='page' value='" . $page . "' />
				<input type='hidden' name='column' value='" . $column . "' />
				<input type='hidden' name='ascending' value='" . $ascending . "' />
				<input type='hidden' name='findValues' value='" . implode(':', getFindArray()) . "' />
				<td><button type='reset' class='btn btn-primary' name='clear'>Clear</button></td>
				<td><button type='submit' class='btn btn-primary' name='is' value='ekle'>New Student</button></td>
				</tr>

			</form>
			<form method='GET'>
				
				<tr style='background: rgba(150, 255, 150, .3)'>
				<td><input type='text' name='findNo' /></td>
				<td><input type='text' name='findName'  /></td>
				<td><input type='text' name='findSurname' /></td>
				<td><input type='text' name='findDepartment' /></td>
				<input type='hidden' name='column' value='" . $column . "' />
				<input type='hidden' name='ascending' value='" . $ascending . "' />
				<td><button type='submit' class='btn btn-primary' name='clear'>Clear</button></td>
				<td><button type='submit' class='btn btn-primary' name='is' value='bul'>Find</button></td>
				</tr>

			</form>";
				
			while($row = mysqli_fetch_array($retval, MYSQLI_ASSOC)){
				echo "<tr>";
				echo "<td>" . $row['no'] . "</td>";
				echo "<td>" . $row['name'] . "</td>";
				echo "<td>" . $row['surname'] . "</td>";
				echo "<td>" . $row['department'] . "</td>";
				echo "<td> <form method='GET'>
					<input type='hidden' name='no' value='" . $row['no'] . "' />
					<input type='hidden' name='name' value='" . $row['name'] . "' />
					<input type='hidden' name='surname' value='" . $row['surname'] . "' />
					<input type='hidden' name='department' value='" . $row['department'] . "' />
					<input type='hidden' name='findValues' value='" . implode(':', getFindArray()) . "' />
					<input type='hidden' name='page' value='" . $page . "' />
					<input type='hidden' name='column' value='" . $column . "' />
					<input type='hidden' name='ascending' value='" . $ascending . "' />
					<button type='submit' class='btn btn-primary' name='is' value='degistir'>Update</button>
					</form> </td>";
				$pagedel = (mysqli_num_rows($retval) == 1 && $page != 1)? $page - 1 : $page;
				echo "<td> <form method='GET'>
					<input type='hidden' name='no' value='" . $row['no'] . "' />
					<input type='hidden' name='page' value='" . $pagedel . "' />
					<input type='hidden' name='column' value='" . $column . "' />
					<input type='hidden' name='ascending' value='" . $ascending . "' />
					<input type='hidden' name='findValues' value='" . implode(':', getFindArray()) . "' />
					<button type='submit' class='btn btn-primary' name='is' value='sil'>Delete</button>
					</form> </td>";
				echo "</tr>";
			}
			
		echo "</tbody>
			</table>
			<div class='center'>";
				if($page != 1){
					echo "<a class='btn btn-primary round-button' href='StudentList.php?page=1&column=" . $column . "&ascending=" . $ascending . "&findValues=" . implode(':', getFindArray()) . "'>&lt;&lt;</a>";
					echo "<a class='btn btn-primary round-button' href='StudentList.php?page=" . ($page - 1) . "&column=" . $column . "&ascending=" . $ascending . "&findValues=" . implode(':', getFindArray()) . "'>&lt;</a>";
				}
				for($count = 1; $count <= $pageCount; $count++){
					if($count == $page){
						echo "<a class='btn btn-primary round-button disabled' href='StudentList.php?page=" . $count . "&column=" . $column . "&ascending=" . $ascending . "&findValues=" . implode(':', getFindArray()) . "'>" . $count . "</a>";
						continue;
					}						
					echo "<a class='btn btn-primary round-button' href='StudentList.php?page=" . $count . "&column=" . $column . "&ascending=" . $ascending . "&findValues=" . implode(':', getFindArray()) . "'>" . $count . "</a>";
				}
				if($page != $pageCount){
					echo "<a class='btn btn-primary round-button' href='StudentList.php?page=" . ($page + 1) . "&column=" . $column . "&ascending=" . $ascending . "&findValues=" . implode(':', getFindArray()) . "'>&gt;</a>";
					echo "<a class='btn btn-primary round-button' href='StudentList.php?page=" . $pageCount . "&column=" . $column . "&ascending=" . $ascending . "&findValues=" . implode(':', getFindArray()) . "'>&gt;&gt;</a>";
				}
			echo "</div>";
			
			
		
		mysqli_close($connect);
	}

	function form($no, $ad, $soyad, $department, $page, $column, $ascending){
?>
	
		<h5>Ogrenci Guncelleme</h5>
		<form action=''>
			<input type='hidden' name='no' value='<?php echo $no; ?>'>
			<input type='hidden' name='page' value='<?php echo $page; ?>' />
			<input type='hidden' name='column' value='<?php echo $column; ?>' />
			<input type='hidden' name='ascending' value='<?php echo $ascending; ?>' />
			<input type='hidden' name='findValues' value='<?php echo $_GET['findValues']; ?>' />
			<input type='hidden' name='is' value='guncelle'>
			<table class='table' style='width:20%'>
				<tr><td><?php echo $_GET['findValues']; ?></td></tr>
				<tr><td>NO</td><td><input disabled name='ogrno' type='text' value='<?php echo $no; ?>'></td></tr>
				<tr><td>Name</td><td><input name='name' type='text' value='<?php echo $ad; ?>'></td></tr>
				<tr><td>Surname</td><td><input name='surname' type='text' value='<?php echo $soyad; ?>'></td></tr>
				<tr><td>Department</td><td><input name='department' type='text' value='<?php echo $department; ?>'></td></tr>
				<tr><td></td><td style='text-align:right'><input name='gonder' type='submit' class='btn btn-primary' value='Update'></td></tr>
			</table>
		</form>
<?php
	}
	
	function connect(){
		$host = "localhost";
		$user = "root";
		$password = null;
		$dbname = "university";
		
		$connect = mysqli_connect($host, $user, $password, $dbname);
		
		if(!$connect)
			die("Could not connect: " . mysqli_error($connect));
		
		$query = "set names 'utf8'";
		mysqli_query($connect, $query);
		
		return $connect;
	}
		
?>