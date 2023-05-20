<?php
require APP . 'model/db-model.php';
require APP . 'model/universal.php';
$model = new MiniAPI($this->db);

/**
 * Agent is accepting a specific check-in
 */
if(isset($_POST['trigger']) && ($_SESSION['userinfo']->type == "Agent"))
{
	$attendanceMarks = $model->seeMyMarks($_SESSION['userinfo']->username);
	?>
	<div class="modal-dialog">
	<div class="modal-content">
		<div class="modal-header">
			<strong>Past 10 Exceptions</strong>
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
		</div>
		<div class="modal-body">
			<table class="table table-condensed">
				<tr>
					<th>Mark</th>
					<th>Started</th>
					<th>Ended</th>
					<th>Duration</th>
					<th>Date</th>
					<tr/>
					<?
					foreach($attendanceMarks as $row)
					{
						?>
						<tr>
							<td><? echo $row->mark; ?></td>
							<td><? echo $row->started; ?></td>
							<td><? echo $row->ended; ?></td>
							<td><? echo $row->duration; ?></td>
							<td><? echo $row->date_of_exception; ?></td>
						</tr>
						<?
					}
					?>
				</table>
			</div>
		</form>
	</div>
</div>
<?
}