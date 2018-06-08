# TALS #

The TALS-Plugin provides an attendance logging system by using smartphones. 

The 'THM Attendance Logging System' activity module enables a teacher to take attendance during class and students to view their own attendance record.

The teacher can create multiple appointments of different types (e.g. 'Lecture' or 'Excercise'). If the teacher wants to take the attendance they can specify a PIN and provide it to the students. After the PIN is set active each student is able to commit their attendance by themself. The teacher then can check the list and is able to change their status.

## Dev Environment ##
You can use the docker files inside the `docker` folder to start up a development environment.
The `moodle-mariadb-compose.yml` file will start up a ready to use, plain moodle site. The `docker-compose.yml` file will start just an moodle ready apache with postgres and mount ah `html` folder that you must create inside the `docker` folder. You can download your desired moodle version and put it inside this folder.  


## License ##
Licensed under the GNU General Public License version 3.

You should have received a copy of the GNU General Public License along with
this program.  If not, see <http://www.gnu.org/licenses/>.

## Developers ##
**Supervised by**: Frank Kammer and Andrej Sajenko

**Originaly designed by**: Christian Breternitz, Lars Herwegh, Johannes Meintrup and Jonas Nimmerfroh
