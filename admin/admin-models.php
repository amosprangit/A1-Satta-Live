<?php
// /admin/includes/admin-modals.php

function renderModals()
{
    ?>
    <!-- Edit Game Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <h3>✏️ Update Game Result</h3>
            <form method="POST">
                <input type="hidden" name="update_result" value="1">
                <input type="hidden" name="game_name" id="editGameName">
                <div class="form-group"><label>Game Name:</label><input type="text" id="editGameNameDisplay" disabled></div>
                <div class="form-group"><label>Display Name:</label><input type="text" name="display_name"
                        id="editDisplayName" required></div>
                <div class="form-group"><label>Yesterday Result:</label><input type="text" name="yesterday_result"
                        id="editYesterday" placeholder="--"></div>
                <div class="form-group"><label>Today Result:</label><input type="text" name="today_result" id="editToday"
                        placeholder="Enter new result number" required></div>
                <div class="form-group"><label>Result Time:</label><input type="text" name="result_time" id="editTime"
                        placeholder="e.g. 5:15 PM"></div>
                <div class="modal-actions">
                    <button type="submit" class="btn-save">💾 Update Result</button>
                    <button type="button" class="btn-cancel" onclick="closeEditModal()">❌ Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Timing Modal -->
    <div id="timingEditModal" class="modal">
        <div class="modal-content">
            <h3>✏️ Edit Game Timing</h3>
            <form method="POST">
                <input type="hidden" name="update_timing" value="1">
                <input type="hidden" name="timing_id" id="timingEditId">
                <div class="form-group"><label>Game Name:</label><input type="text" name="timing_game_name"
                        id="timingEditGameName" required></div>
                <div class="form-group"><label>Time:</label><input type="text" name="timing_time" id="timingEditTime"
                        required></div>
                <div class="form-group"><label>Emoji:</label><input type="text" name="timing_emoji" id="timingEditEmoji">
                </div>
                <div class="form-group"><label>Active:</label>
                    <select name="timing_is_active" id="timingEditActive">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
                <div class="modal-actions">
                    <button type="submit" class="btn-save">💾 Save</button>
                    <button type="button" class="btn-cancel" onclick="closeTimingEditModal()">❌ Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Rate Modal -->
    <div id="rateEditModal" class="modal">
        <div class="modal-content">
            <h3>✏️ Edit Game Rate</h3>
            <form method="POST">
                <input type="hidden" name="update_rate" value="1">
                <input type="hidden" name="rate_id" id="rateEditId">
                <div class="form-group"><label>Rate Type:</label><input type="text" name="rate_type" id="rateEditType"
                        required></div>
                <div class="form-group"><label>Rate Value:</label><input type="text" name="rate_value" id="rateEditValue"
                        required></div>
                <div class="form-group"><label>Active:</label>
                    <select name="rate_is_active" id="rateEditActive">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
                <div class="modal-actions">
                    <button type="submit" class="btn-save">💾 Save</button>
                    <button type="button" class="btn-cancel" onclick="closeRateEditModal()">❌ Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Table Modal -->
    <div id="editTableModal" class="modal">
        <div class="modal-content">
            <h3>✏️ Edit Table</h3>
            <form method="POST">
                <input type="hidden" name="update_custom_table" value="1">
                <input type="hidden" name="table_id" id="editTableId">
                <div class="form-group"><label>Table Name:</label><input type="text" name="table_name" id="editTableName"
                        required></div>
                <div class="form-group"><label>Description:</label><input type="text" name="table_description"
                        id="editTableDescription"></div>
                <div class="modal-actions">
                    <button type="submit" class="btn-save">💾 Save</button>
                    <button type="button" class="btn-cancel" onclick="closeEditTableModal()">❌ Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Table Game Modal -->
    <div id="editTableGameModal" class="modal">
        <div class="modal-content">
            <h3>✏️ Edit Table Game</h3>
            <form method="POST">
                <input type="hidden" name="update_table_game" value="1">
                <input type="hidden" name="game_id" id="editTableGameId">
                <input type="hidden" name="table_id" id="editTableGameTableId">
                <div class="form-group"><label>Game Name:</label><input type="text" name="game_name" id="editTableGameName"
                        required></div>
                <div class="form-group"><label>Display Name:</label><input type="text" name="display_name"
                        id="editTableGameDisplayName"></div>
                <div class="form-group"><label>Yesterday Result:</label><input type="text" name="yesterday_result"
                        id="editTableGameYesterday" placeholder="--"></div>
                <div class="form-group"><label>Today Result:</label><input type="text" name="today_result"
                        id="editTableGameToday" placeholder="WAIT"></div>
                <div class="form-group"><label>Result Time:</label><input type="text" name="result_time"
                        id="editTableGameTime" placeholder="e.g. 5:15 PM"></div>
                <div class="modal-actions">
                    <button type="submit" class="btn-save">💾 Save</button>
                    <button type="button" class="btn-cancel" onclick="closeEditTableGameModal()">❌ Cancel</button>
                </div>
            </form>
        </div>
    </div>
    <?php
}
?>