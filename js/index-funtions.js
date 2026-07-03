function openPlayTimeEditor() {
  document.getElementById("playTimeEditorModal").style.display = "flex";
}

function closePlayTimeEditor() {
  document.getElementById("playTimeEditorModal").style.display = "none";
}

function showPlayTimeTab(tab) {
  document.getElementById("timingsTab").style.display =
    tab === "timings" ? "block" : "none";
  document.getElementById("ratesTab").style.display =
    tab === "rates" ? "block" : "none";
  document.getElementById("tabTimingsBtn").style.background =
    tab === "timings" ? "#ffd700" : "#e0e0e0";
  document.getElementById("tabRatesBtn").style.background =
    tab === "rates" ? "#ffd700" : "#e0e0e0";
}

function addTiming() {
  const game = document.getElementById("newTimingGame").value;
  const time = document.getElementById("newTimingTime").value;
  const emoji = document.getElementById("newTimingEmoji").value || "😇";

  if (!game || !time) {
    alert("Please fill all fields");
    return;
  }

  fetch("ajax-handler.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: `action=add_timing&game_name=${encodeURIComponent(game)}&timing=${encodeURIComponent(time)}&emoji=${encodeURIComponent(emoji)}`,
  })
    .then((r) => r.json())
    .then((data) => {
      if (data.success) {
        location.reload();
      } else {
        alert("Error: " + data.message);
      }
    });
}

function editTiming(id) {
  const row = document.getElementById("timing-row-" + id);
  const cells = row.querySelectorAll("td");
  document.getElementById("editTimingId").value = id;
  document.getElementById("editTimingGame").value = cells[1].innerText.trim();
  document.getElementById("editTimingTime").value = cells[2].innerText.trim();
  document.getElementById("editTimingEmoji").value = cells[0].innerText.trim();
  document.getElementById("editTimingForm").style.display = "block";
  document.getElementById("editRateForm").style.display = "none";
}

function updateTiming() {
  const id = document.getElementById("editTimingId").value;
  const game = document.getElementById("editTimingGame").value;
  const time = document.getElementById("editTimingTime").value;
  const emoji = document.getElementById("editTimingEmoji").value;

  fetch("ajax-handler.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: `action=update_timing&id=${id}&game_name=${encodeURIComponent(game)}&timing=${encodeURIComponent(time)}&emoji=${encodeURIComponent(emoji)}`,
  })
    .then((r) => r.json())
    .then((data) => {
      if (data.success) {
        location.reload();
      } else {
        alert("Error: " + data.message);
      }
    });
}

function deleteTiming(id) {
  if (!confirm("Delete this timing?")) return;

  fetch("ajax-handler.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: `action=delete_timing&id=${id}`,
  })
    .then((r) => r.json())
    .then((data) => {
      if (data.success) {
        location.reload();
      } else {
        alert("Error: " + data.message);
      }
    });
}

function cancelEditTiming() {
  document.getElementById("editTimingForm").style.display = "none";
}

// ============ RATES CRUD ============
function addRate() {
  const type = document.getElementById("newRateType").value;
  const value = document.getElementById("newRateValue").value;

  if (!type || !value) {
    alert("Please fill all fields");
    return;
  }

  fetch("ajax-handler.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: `action=add_rate&rate_type=${encodeURIComponent(type)}&rate_value=${encodeURIComponent(value)}`,
  })
    .then((r) => r.json())
    .then((data) => {
      if (data.success) {
        location.reload();
      } else {
        alert("Error: " + data.message);
      }
    });
}

function editRate(id) {
  const row = document.getElementById("rate-row-" + id);
  const cells = row.querySelectorAll("td");
  document.getElementById("editRateId").value = id;
  document.getElementById("editRateType").value = cells[0].innerText.trim();
  document.getElementById("editRateValue").value = cells[1].innerText.trim();
  document.getElementById("editRateForm").style.display = "block";
  document.getElementById("editTimingForm").style.display = "none";
}

function updateRate() {
  const id = document.getElementById("editRateId").value;
  const type = document.getElementById("editRateType").value;
  const value = document.getElementById("editRateValue").value;

  fetch("ajax-handler.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: `action=update_rate&id=${id}&rate_type=${encodeURIComponent(type)}&rate_value=${encodeURIComponent(value)}`,
  })
    .then((r) => r.json())
    .then((data) => {
      if (data.success) {
        location.reload();
      } else {
        alert("Error: " + data.message);
      }
    });
}

function deleteRate(id) {
  if (!confirm("Delete this rate?")) return;

  fetch("ajax-handler.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: `action=delete_rate&id=${id}`,
  })
    .then((r) => r.json())
    .then((data) => {
      if (data.success) {
        location.reload();
      } else {
        alert("Error: " + data.message);
      }
    });
}

function cancelEditRate() {
  document.getElementById("editRateForm").style.display = "none";
}
