// ============================================
// EDIT GAME MODAL - FIXED
// ============================================
function openEditModal(game, yesterday, today, time, displayName) {
  document.getElementById("editGameName").value = game;
  document.getElementById("editGameNameDisplay").value = game.toUpperCase();
  document.getElementById("editDisplayName").value = displayName || "";
  document.getElementById("editYesterday").value = yesterday || "--";
  document.getElementById("editToday").value = today || "WAIT";
  document.getElementById("editTime").value = time || "";
  document.getElementById("editModal").style.display = "flex";
}

function closeEditModal() {
  document.getElementById("editModal").style.display = "none";
}

// ============================================
// DYNAMIC GAME SELECTOR - FIXED
// ============================================
function loadGameData(gameName) {
  if (!gameName) {
    document.getElementById("gameInfoDisplay").style.display = "none";
    return;
  }

  document.getElementById("gameInfoDisplay").style.display = "block";
  document.getElementById("currentStatus").innerHTML = "⏳ Loading...";

  fetch("ajax/get-game-info.php?game=" + encodeURIComponent(gameName))
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        document.getElementById("editDisplayName").value =
          data.display_name || "";
        document.getElementById("editResultTime").value =
          data.result_time || "";
        document.getElementById("editYesterdayResult").value =
          data.yesterday_result || "--";
        document.getElementById("editTodayResult").value =
          data.today_result || "WAIT";

        let sourceInfo = data.source || "Main Table";
        document.getElementById("currentStatus").innerHTML =
          "📊 <strong>Today:</strong> " +
          (data.today_result || "WAIT") +
          " | <strong>Yesterday:</strong> " +
          (data.yesterday_result || "--") +
          " | <strong>Time:</strong> " +
          (data.result_time || "--") +
          " | <strong>Source:</strong> " +
          sourceInfo;
      } else {
        document.getElementById("currentStatus").innerHTML =
          "⚠️ Game not found or inactive";
      }
    })
    .catch((error) => {
      console.log("Error loading game data:", error);
      document.getElementById("currentStatus").innerHTML =
        "❌ Error loading game data";
    });
}

// ============================================
// CLEAR FORM - FIXED
// ============================================
function clearForm() {
  document.getElementById("selectGameName").value = "";
  document.getElementById("editDisplayName").value = "";
  document.getElementById("editResultTime").value = "";
  document.getElementById("editYesterdayResult").value = "";
  document.getElementById("editTodayResult").value = "";
  document.getElementById("gameInfoDisplay").style.display = "none";
  document.getElementById("currentStatus").innerHTML =
    "Select a game to view details";
}

// ============================================
// TIMING EDIT MODAL
// ============================================
function openTimingEditModal(id, gameName, timing, emoji, isActive) {
  document.getElementById("timingEditId").value = id;
  document.getElementById("timingEditGameName").value = gameName;
  document.getElementById("timingEditTime").value = timing;
  document.getElementById("timingEditEmoji").value = emoji || "😇";
  document.getElementById("timingEditActive").value = isActive;
  document.getElementById("timingEditModal").style.display = "flex";
}

function closeTimingEditModal() {
  document.getElementById("timingEditModal").style.display = "none";
}

// ============================================
// RATE EDIT MODAL
// ============================================
function openRateEditModal(id, rateType, rateValue, isActive) {
  document.getElementById("rateEditId").value = id;
  document.getElementById("rateEditType").value = rateType;
  document.getElementById("rateEditValue").value = rateValue;
  document.getElementById("rateEditActive").value = isActive;
  document.getElementById("rateEditModal").style.display = "flex";
}

function closeRateEditModal() {
  document.getElementById("rateEditModal").style.display = "none";
}

// ============================================
// TABLE EDIT MODAL
// ============================================
function openEditTableModal(id, name, description) {
  document.getElementById("editTableId").value = id;
  document.getElementById("editTableName").value = name || "";
  document.getElementById("editTableDescription").value = description || "";
  document.getElementById("editTableModal").style.display = "flex";
}

function closeEditTableModal() {
  document.getElementById("editTableModal").style.display = "none";
}

// ============================================
// TABLE GAME EDIT MODAL
// ============================================
function openTableGameEditModal(
  id,
  tableId,
  gameName,
  displayName,
  yesterday,
  today,
  time,
) {
  document.getElementById("editTableGameId").value = id;
  document.getElementById("editTableGameTableId").value = tableId;
  document.getElementById("editTableGameName").value = gameName;
  document.getElementById("editTableGameDisplayName").value = displayName || "";
  document.getElementById("editTableGameYesterday").value = yesterday || "--";
  document.getElementById("editTableGameToday").value = today || "WAIT";
  document.getElementById("editTableGameTime").value = time || "";
  document.getElementById("editTableGameModal").style.display = "flex";
}

function closeEditTableGameModal() {
  document.getElementById("editTableGameModal").style.display = "none";
}

// ============================================
// AUTO-LOAD ON PAGE LOAD
// ============================================
document.addEventListener("DOMContentLoaded", function () {
  const select = document.getElementById("selectGameName");
  if (select && select.value) {
    loadGameData(select.value);
  }
});

// ============================================
// CLOSE MODALS ON CLICK OUTSIDE
// ============================================
window.onclick = function (event) {
  if (event.target.classList.contains("modal")) {
    event.target.style.display = "none";
  }
};

// ============================================
// CLOSE MODALS WITH ESCAPE KEY
// ============================================
document.addEventListener("keydown", function (event) {
  if (event.key === "Escape") {
    document.querySelectorAll(".modal").forEach(function (el) {
      el.style.display = "none";
    });
  }
});

// ============================================
// ADD: FORCE REFRESH FOR LIVE BOX (Optional)
// ============================================
// This can be used after updating a result to refresh the Live Box
function refreshLiveBox() {
  const liveBox = document.querySelector(".live-box");
  if (liveBox) {
    // Add a flash effect to indicate update
    liveBox.style.transition = "background 0.3s";
    liveBox.style.background = "#2a2a4e";
    setTimeout(() => {
      liveBox.style.background = "";
    }, 500);
  }
}
