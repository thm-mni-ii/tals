<style type="text/css">
		
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

		.rahmen {
		    border: 1px solid #ddd;
		    padding: 4px;
		    margin-bottom: 5px;
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
		<li id="li_active"><a href="termin.php">Termin</a></li>
		<li><a href="terminHinzu.php">Termin hinzufügen</a></li>
		<li><a href="bericht.php">Bericht</a></li>
	</ul>

	<!--INHALT-->
	<div id="Termin" class="tabcontent">
	  	<h3>Termin</h3>

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
				<th>Bearbeiten</th>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			</tr>
			</tbody>
		  </table>

	</div>

