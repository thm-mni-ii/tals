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
		<li id="li_active"><a href="TerminHinzu.php">Termin hinzufügen</a></li>
		<li><a href="bericht.php">Bericht</a></li>
	</ul>

	<div id="TerminHinzu" class="tabcontent">
	 	<h3>Termin hinzufügen</h3>
	 	<!-- ART -->
	 	<div class="rahmen">
	 		<b>Neuer Termin</b>
	  		<table>
	  			<tr>
	  			<td class="description_cell">
	  				<p class="description">Art</p>  
	  			</td>
	  			<td>
			  		<select>
						<option value="uebung">Übung</option>
						<option value="praktikum">Praktikum</option>
						<option value="vorlesung">Vorlesung</option>
					</select> 
				</td>
	  			</tr>
	  		</table>
	  	</div>

	  	<!-- WIEDERKEHRENDE LERNEINHEIT -->
	  	<div class="rahmen">
	  		<b>Wiederkehrende Lerneinheit</b>
	  		<table>
	  			<!-- ZEILE 1 -->
	  			<tr>
	  			<td class="description_cell"> 
	  			</td>
	  			<td>
	  				<input type="checkbox" name="wiederholen" value="wiederholen"> Die obige Lerneinheit wie folgt wiederholen <br>
	  			</td>
	  			</tr>
	  			<!-- ZEILE 2 -->
	  			<tr>
	  			<td class="description_cell">
	  				<p class="description">Wiederholen am</p>
	  			</td>
	  			<td>
	  				<input type="checkbox" name="Montag" value="mon">Montag 
	  				<input type="checkbox" name="Dienstag" value="die">Dienstag 
	  				<input type="checkbox" name="Mittwoch" value="mit">Mittwoch 
	  				<input type="checkbox" name="Donnerstag" value="don">Donnerstag 
	  				<input type="checkbox" name="Freitag" value="fre">Freitag 
	  				<input type="checkbox" name="Samstag" value="sam">Samstag <br>
	  			</td>
	  			</tr>
	  			<!-- ZEILE 3 -->
	  			<tr>
	  			<td class="description_cell">
	  				<p class="description">Wiederholen alle</p>
	  			</td>
	  			<td>
	  				<select>
						<option value="1">1</option>
						<option value="2">2</option>
						<option value="3">3</option>
						<option value="4">4</option>
					</select> 
					<p class="description">Woche(n)</p>
	  			</td>
	  			</tr>
	  			<!-- ZEILE 4 -->
	  			<tr>
	  			<td class="description_cell">
	  				<p class="description">Wiederholen bis</p>
	  			</td>
	  			<td>
	  				<input type="date" id="endDate">
	  			</td>
	  			</tr>
	  		</table>
	  	</div>

	  	<!-- GRUPPIERTE TERMINE -->
	  	<div class="rahmen">
	  		<b>Gruppierte Termine</b>
	  		<table>
	  			<!-- ZEILE 1 -->
	  			<tr>
	  			<td class="description_cell"> 
	  				<p class="description">Gruppe</p>
	  			</td>
	  			<td>
	  				<select>
						<option value="gruppe1">Gruppe 1</option>
						<option value="gruppe2">Gruppe 2</option>
						<option value="gruppe3">Gruppe 3</option>
						<option value="gruppe4">Gruppe 4</option>
					</select> 
	  			</td>
	  			</tr>
	  			<!-- ZEILE 2 -->
	  			<tr>
	  			<td class="description_cell"> 
	  				<p class="description">Gruppe- hinzufügen</p>
	  			</td>
	  			<td>
	  				<input type="text" id="newgroup" name="newgroup">
	  				<input type="submit" id="setnewgroup" value="anlegen">
	  			</td>
	  			</tr>
	  		</table>
	  	</div>

		<!-- PIN (Student recording) -->
	  	<div class="rahmen">
	  		<b>PIN</b>
	  		<table>
	  			<!-- ZEILE 1 -->
	  			<tr>
	  			<td class="description_cell"> 
	  			</td>
	  			<td>
	  				<input type="checkbox" name="pflicht" value="pflicht">ist anwesenheitspflichtig 
	  			</td>
	  			</tr>
	  			<!-- ZEILE 2 -->
	  			<tr>
	  			<td class="description_cell"> 
	  				<p class="description">PIN</p> 
	  			</td>
	  			<td>
	  				<input type="text" id="pin" name="pin">
	  				<input type="checkbox" name="randomPIN" value="randomPIN">Zufallspin
	  			</td>
	  			</tr>
	  			<!-- ZEILE 3 -->
	  			<tr>
	  			<td class="description_cell"> 
	  				<p class="description">Dauer</p>
	  			</td>
	  			<td>
	  				<select>
						<option value="value1">5</option>
						<option value="value2">10</option>
						<option value="value3">15</option>
						<option value="value4">20</option>
						<option value="value5">25</option>
						<option value="value6">30</option>
						<option value="value7">35</option>
						<option value="value8">40</option>
						<option value="value9">45</option>
						<option value="value10">50</option>
						<option value="value11">55</option>
						<option value="value12">60</option>
					</select> 
					<p class="description">Minuten</p>
	  			</td>
	  			</tr>
	  			<!-- ZEILE 4 -->
	  			<tr>
	  			<td class="description_cell"> 
	  			</td>
	  			<td>
	  				<input type="submit" id="setpin" value="anlegen">
	  			</td>
	  			</tr>
	  		</table>
	  	</div>	  	
	</div>