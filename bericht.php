<style type="text/css">
		
		/*TERMIN*/
		#tabelle {
		    font-size: 13px;
		    border-collapse: collapse;
		    width: 100%;
		}

		#tabelle td {
		    border: 1px solid #ddd;
		    padding: 8px;
		}

		#tabelle th {
		    border: 1px solid #ddd;
		    padding: 8px;
		    padding-top: 12px;
		    padding-bottom: 12px;
		    text-align: left;
		    background-color: #333;
		    color: white;
		}

	    tr:hover {
	    	cursor: pointer;
	      	background-color: #ccc;
	    }

		/*TERMIN HINZUFÜGEN*/
		.rahmen {
		    border: 1px solid #ddd;
		    padding: 4px;
		    margin-bottom: 5px;
		}

		.description {
		    display:inline;
		}

		.description_cell {
		    width:150px;
		}
		
		ul {
		    list-style-type: none;
		    margin: 0;
		    padding: 0;
		    overflow: hidden;
		    background-color: #333;
		}

		li {
		    float: left;
		}

		li a {
		    display: inline-block;
		    color: white;
		    text-align: center;
		    padding: 14px 16px;
		    text-decoration: none;
		}

		li a:hover {
		    background-color: #111;
		}

		#li_active {
			background-color: #80ba24;
		}

	</style>

	<ul>
		<li><a href="termin.php">Termin</a></li>
		<li><a href="terminHinzu.php">Termin hinzufügen</a></li>
		<li id="li_active"><a href="bericht.php">Bericht</a></li>
	</ul>

	<div id="Bericht" class="tabcontent">
	  	<h3>Bericht</h3>
	  	
	  	<table id="tabelle">
			<tbody>
			<tr>
				<th><p>ID</p></th>
				<th><p>Name</p></th>
				<th><p>Beschreibung</p></th>
				<th>Start</th>
				<th>Ende</th>
				<th>Duration</th>
				<th>Typ</th>
				<th>Anz. Teilnehmer</th>
			</tr>
			<tr>
				<td>1</td>
				<td><a href="kursBericht.php">Datenbanksysteme</a></td>
				<td>Hallo213</td>
				<td>jetzt</td>
				<td>dann</td>
				<td>45 min</td>
				<td>Vorlesung</td>
				<td>43</td>
			</tr>
			<tr>
				<td>2</td>
				<td><a href="#">Analysis</a></td>
				<td>Hallo9876</td>
				<td>jetzt</td>
				<td>dann</td>
				<td>60 min</td>
				<td>Uebung</td>
				<td>8</td>
			</tr>
			</tbody>
	  	</table>
	</div> 