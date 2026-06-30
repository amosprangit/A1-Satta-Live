function openEditModal(game, yesterday, today, time, displayName) {
  console.log("Opening edit for game: " + game);
  document.getElementById("editGameName").value = game;
  document.getElementById("editGameNameDisplay").value = game.toUpperCase();
  document.getElementById("editDisplayName").value = displayName || "";
  document.getElementById("editYesterdayDisplay").textContent =
    yesterday || "--";
  document.getElementById("editTodayDisplay").textContent = today || "WAIT";
  document.getElementById("editTodayDisplay").style.color =
    today === "WAIT" || today === "-1" || !today ? "#d32f2f" : "#28a745";
  document.getElementById("editToday").value = "";
  document.getElementById("editToday").placeholder =
    today === "WAIT" ? "Enter new result" : "Current: " + today;
  document.getElementById("editTime").value = time;
  document.getElementById("editModal").style.display = "flex";
}

function closeEditModal() {
  document.getElementById("editModal").style.display = "none";
}

// ===== TIMING EDIT MODAL =====
function openTimingEditModal(id, gameName, timing, emoji, isActive) {
  document.getElementById("timingEditId").value = id;
  document.getElementById("timingEditGameName").value = gameName;
  document.getElementById("timingEditTime").value = timing;
  document.getElementById("timingEditEmoji").value = emoji;
  document.getElementById("timingEditActive").value = isActive;
  document.getElementById("timingEditModal").style.display = "flex";
}

function closeTimingEditModal() {
  document.getElementById("timingEditModal").style.display = "none";
}

// ===== RATE EDIT MODAL =====
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

// ===== MULTIPLE RESULT EDIT MODAL =====
function openMultipleResultEditModal(id, gameName, date, number, time) {
  document.getElementById("mrEditId").value = id;
  document.getElementById("mrEditGameName").value = gameName;
  document.getElementById("mrEditDate").value = date;
  document.getElementById("mrEditNumber").value = number;
  document.getElementById("mrEditTime").value = time || "";
  document.getElementById("multipleResultEditModal").style.display = "flex";
}

function closeMultipleResultEditModal() {
  document.getElementById("multipleResultEditModal").style.display = "none";
}

// ===== CLOSE MODALS ON CLICK OUTSIDE =====
window.onclick = function (event) {
  if (event.target.classList.contains("modal")) {
    event.target.style.display = "none";
  }
};

// ===== CLOSE MODALS WITH ESCAPE KEY =====
document.addEventListener("keydown", function (event) {
  if (event.key === "Escape") {
    document.querySelectorAll(".modal").forEach(function (el) {
      el.style.display = "none";
    });
  }
});

// ============================================
// OPTIONAL: AUTO-REFRESH ON SUCCESS
// ============================================
// Uncomment this if you want auto-refresh after update
/*
setTimeout(function() {
    if (document.querySelector('.success-msg')) {
        setTimeout(function() {
            location.reload();
        }, 3000);
    }
}, 1000);
*/
