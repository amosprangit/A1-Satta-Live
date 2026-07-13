// ============ CHART FUNCTIONS ============

// Month names array for display
const monthNames = [
  "January",
  "February",
  "March",
  "April",
  "May",
  "June",
  "July",
  "August",
  "September",
  "October",
  "November",
  "December",
];

// Load chart data when check button is clicked
function loadChartData() {
  const game = document.getElementById("chartGameSelect").value;
  const year = document.getElementById("chartYearSelect").value;
  const month = document.getElementById("chartMonthSelect").value;

  if (!game) {
    alert("Please select a game!");
    return;
  }

  const container = document.getElementById("chartResultsContainer");
  const display = document.getElementById("chartDataDisplay");

  // Show loading state
  container.style.display = "block";
  display.innerHTML = `
        <div style="text-align: center; padding: 60px 20px; background: #f8f9fa; border-radius: 15px; border: 2px dashed #ccc;">
            <div style="font-size: 48px; margin-bottom: 20px;">⏳</div>
            <div style="font-size: 18px;">Loading chart data for ${game.toUpperCase()}...</div>
        </div>
    `;

  // Update title
  document.getElementById("chartTitle").textContent =
    game.toUpperCase() +
    " RESULT CHART FOR " +
    monthNames[parseInt(month) - 1] +
    " " +
    year;

  // Fetch chart data
  fetch("get-chart-data.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: `game=${encodeURIComponent(game)}&year=${encodeURIComponent(year)}&month=${encodeURIComponent(month)}`,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        renderChartTable(data.data, game, year, month);
      } else {
        display.innerHTML = `
                <div style="text-align: center; padding: 60px 20px; background: #fff8e7; border-radius: 15px; border: 2px dashed #ffd700;">
                    <div style="font-size: 48px; margin-bottom: 20px;">📊</div>
                    <h3 style="color: #c49a00;">${data.message}</h3>
                    <button onclick="window.location.href='admin-dashboard.php?tab=chart'" style="
                        margin-top: 20px;
                        padding: 12px 30px;
                        background: #28a745;
                        color: #fff;
                        border: none;
                        border-radius: 40px;
                        font-weight: bold;
                        cursor: pointer;
                        font-size: 16px;
                    ">📊 Go to Admin Dashboard</button>
                </div>
            `;
      }
    })
    .catch((error) => {
      display.innerHTML = `
            <div style="text-align: center; padding: 60px 20px; color: #dc3545;">
                <div style="font-size: 48px; margin-bottom: 20px;">❌</div>
                <div style="font-size: 18px;">Error loading chart data: ${error}</div>
            </div>
        `;
    });
}

// Render the chart table
function renderChartTable(data, game, year, month) {
  const display = document.getElementById("chartDataDisplay");

  if (!data || data.length === 0) {
    display.innerHTML = `
            <div style="text-align: center; padding: 60px 20px; background: #fff8e7; border-radius: 15px; border: 2px dashed #ffd700;">
                <div style="font-size: 48px; margin-bottom: 20px;">📊</div>
                <h3 style="color: #c49a00;">No chart data found for ${game.toUpperCase()}</h3>
                <p style="color: #666;">No results found for this game in ${monthNames[parseInt(month) - 1]} ${year}.</p>
                <button onclick="window.location.href='admin-dashboard.php?tab=chart'" style="
                    margin-top: 15px;
                    padding: 12px 30px;
                    background: #28a745;
                    color: #fff;
                    border: none;
                    border-radius: 40px;
                    font-weight: bold;
                    cursor: pointer;
                    font-size: 16px;
                ">📊 Go to Admin Dashboard</button>
            </div>
        `;
    return;
  }

  // Calculate statistics
  const results = data.map((item) => parseInt(item.result) || 0);
  const validResults = results.filter((r) => r > 0);
  const max = validResults.length > 0 ? Math.max(...validResults) : 0;
  const min = validResults.length > 0 ? Math.min(...validResults) : 0;
  const sum = validResults.reduce((a, b) => a + b, 0);
  const avg =
    validResults.length > 0 ? (sum / validResults.length).toFixed(1) : 0;

  // Count occurrences of each number
  const frequency = {};
  validResults.forEach((r) => {
    frequency[r] = (frequency[r] || 0) + 1;
  });
  const mostFrequent =
    Object.keys(frequency).sort((a, b) => frequency[b] - frequency[a])[0] ||
    "--";

  // Build table
  let html = `
        <!-- Chart Table -->
        <div style="overflow-x: auto; background: #fff; border-radius: 15px; box-shadow: 0 2px 15px rgba(0,0,0,0.08);">
            <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
                <thead>
                    <tr style="background: #1a1a2e; color: #ffd700;">
                        <th style="padding: 15px; border: 1px solid #333; text-align: center; font-size: 16px;">Date</th>
                        <th style="padding: 15px; border: 1px solid #333; text-align: center; font-size: 16px;">Result</th>
                    </tr>
                </thead>
                <tbody>
          `;

  data.forEach((item, index) => {
    const rowColor = index % 2 === 0 ? "#f9f9f9" : "#ffffff";
    let displayDate = item.chart_date || item.date || "--";

    if (displayDate !== "--") {
      const parts = displayDate.split("-");
      if (parts.length === 3) {
        displayDate = parts[2] + "-" + parts[1];
      }
    }

    const resultNum = parseInt(item.result) || 0;
    const isHigh = resultNum > 50;
    const resultColor = isHigh ? "#28a745" : resultNum > 0 ? "#dc3545" : "#666";

    html += `
            <tr style="background: ${rowColor}; border-bottom: 1px solid #eee; transition: background 0.3s;" 
                onmouseover="this.style.background='#fff8e0'" 
                onmouseout="this.style.background='${rowColor}'">
                <td style="padding: 12px 15px; text-align: center; font-weight: bold; font-size: 16px;">
                    ${displayDate}
                </td>
                <td style="padding: 12px 15px; text-align: center; font-size: 24px; font-weight: bold; color: ${resultColor};">
                    ${item.result || "--"}
                </td>
            </tr>
        `;
  });

  html += `
                </tbody>
            </table>
        </div>
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px; flex-wrap: wrap; gap: 10px;">
            <div style="color: #666; font-size: 14px;">
                📊 Showing ${data.length} entries for ${game.toUpperCase()}
            </div>
            <div>
                <button onclick="window.location.href='chart.php'" style="
                    padding: 10px 25px;
                    background: #6c757d;
                    color: #fff;
                    border: none;
                    border-radius: 40px;
                    font-weight: bold;
                    cursor: pointer;
                    margin-right: 10px;
                ">📅 View Full Chart</button>
                <button onclick="window.location.href='admin-dashboard.php?tab=chart'" style="
                    padding: 10px 25px;
                    background: #ffd700;
                    color: #000;
                    border: none;
                    border-radius: 40px;
                    font-weight: bold;
                    cursor: pointer;
                ">⚙️ Manage Charts</button>
            </div>
        </div>
    `;

  display.innerHTML = html;
}

// ============ PLAY TIME EDITOR FUNCTIONS ============

// Open/Close Modal
function openPlayTimeEditor() {
  document.getElementById("playTimeEditorModal").style.display = "flex";
  document.body.style.overflow = "hidden";
}

function closePlayTimeEditor() {
  document.getElementById("playTimeEditorModal").style.display = "none";
  document.body.style.overflow = "auto";
}

// Tab Switching
function showPlayTimeTab(tab) {
  if (tab === "timings") {
    document.getElementById("timingsTab").style.display = "block";
    document.getElementById("ratesTab").style.display = "none";
    document.getElementById("tabTimingsBtn").style.background = "#ffd700";
    document.getElementById("tabRatesBtn").style.background = "#e0e0e0";
  } else {
    document.getElementById("timingsTab").style.display = "none";
    document.getElementById("ratesTab").style.display = "block";
    document.getElementById("tabTimingsBtn").style.background = "#e0e0e0";
    document.getElementById("tabRatesBtn").style.background = "#ffd700";
  }
}

// ============ TIMINGS CRUD (EMOJI REMOVED) ============
function addTiming() {
  const game = document.getElementById("newTimingGame").value.trim();
  const time = document.getElementById("newTimingTime").value.trim();

  if (!game || !time) {
    alert("Please fill in all fields!");
    return;
  }

  fetch("ajax-handler.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: `action=add_timing&game_name=${encodeURIComponent(game)}&timing=${encodeURIComponent(time)}`,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        location.reload();
      } else {
        alert("Error: " + data.message);
      }
    })
    .catch((error) => alert("Error: " + error));
}

function editTiming(id) {
  const row = document.getElementById("timing-row-" + id);
  if (!row) {
    console.error("Row not found for ID:", id);
    return;
  }

  const cells = row.querySelectorAll("td");
  // cells[0] = Game Name, cells[1] = Timing (emoji removed)
  const game = cells[0].textContent.trim();
  const time = cells[1].textContent.trim();

  document.getElementById("editTimingId").value = id;
  document.getElementById("editTimingGame").value = game;
  document.getElementById("editTimingTime").value = time;
  document.getElementById("editTimingForm").style.display = "block";
  document
    .getElementById("editTimingForm")
    .scrollIntoView({ behavior: "smooth" });
}

function updateTiming() {
  const id = document.getElementById("editTimingId").value;
  const game = document.getElementById("editTimingGame").value.trim();
  const time = document.getElementById("editTimingTime").value.trim();

  if (!id || !game || !time) {
    alert("Please fill in all fields!");
    return;
  }

  fetch("ajax-handler.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: `action=update_timing&id=${id}&game_name=${encodeURIComponent(game)}&timing=${encodeURIComponent(time)}`,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        location.reload();
      } else {
        alert("Error: " + data.message);
      }
    })
    .catch((error) => alert("Error: " + error));
}

function cancelEditTiming() {
  document.getElementById("editTimingForm").style.display = "none";
}

function deleteTiming(id) {
  if (!confirm("Are you sure you want to delete this timing?")) return;

  fetch("ajax-handler.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: `action=delete_timing&id=${id}`,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        location.reload();
      } else {
        alert("Error: " + data.message);
      }
    })
    .catch((error) => alert("Error: " + error));
}

// ============ RATES CRUD ============
function addRate() {
  const type = document.getElementById("newRateType").value.trim();
  const value = document.getElementById("newRateValue").value.trim();

  if (!type || !value) {
    alert("Please fill in all fields!");
    return;
  }

  fetch("ajax-handler.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: `action=add_rate&rate_type=${encodeURIComponent(type)}&rate_value=${encodeURIComponent(value)}`,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        location.reload();
      } else {
        alert("Error: " + data.message);
      }
    })
    .catch((error) => alert("Error: " + error));
}

function editRate(id) {
  const row = document.getElementById("rate-row-" + id);
  if (!row) {
    console.error("Row not found for ID:", id);
    return;
  }

  const cells = row.querySelectorAll("td");
  const type = cells[0].textContent.trim();
  const value = cells[1].textContent.trim();

  document.getElementById("editRateId").value = id;
  document.getElementById("editRateType").value = type;
  document.getElementById("editRateValue").value = value;
  document.getElementById("editRateForm").style.display = "block";
  document
    .getElementById("editRateForm")
    .scrollIntoView({ behavior: "smooth" });
}

function updateRate() {
  const id = document.getElementById("editRateId").value;
  const type = document.getElementById("editRateType").value.trim();
  const value = document.getElementById("editRateValue").value.trim();

  if (!id || !type || !value) {
    alert("Please fill in all fields!");
    return;
  }

  fetch("ajax-handler.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: `action=update_rate&id=${id}&rate_type=${encodeURIComponent(type)}&rate_value=${encodeURIComponent(value)}`,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        location.reload();
      } else {
        alert("Error: " + data.message);
      }
    })
    .catch((error) => alert("Error: " + error));
}

function cancelEditRate() {
  document.getElementById("editRateForm").style.display = "none";
}

function deleteRate(id) {
  if (!confirm("Are you sure you want to delete this rate?")) return;

  fetch("ajax-handler.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: `action=delete_rate&id=${id}`,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        location.reload();
      } else {
        alert("Error: " + data.message);
      }
    })
    .catch((error) => alert("Error: " + error));
}

// ============ MULTIPLE RESULT CRUD ============
function addMultipleResult() {
  const game = document.getElementById("mrGameName").value.trim();
  const date = document.getElementById("mrResultDate").value;
  const number = document.getElementById("mrResultNumber").value.trim();
  const time = document.getElementById("mrResultTime").value.trim();

  if (!game || !date || !number) {
    alert("Please fill in all fields!");
    return;
  }

  fetch("ajax-handler.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: `action=add_multiple_result&game=${encodeURIComponent(game)}&date=${encodeURIComponent(date)}&number=${encodeURIComponent(number)}&time=${encodeURIComponent(time)}`,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        location.reload();
      } else {
        alert("Error: " + data.message);
      }
    })
    .catch((error) => alert("Error: " + error));
}

// Close modals when clicking outside
document.addEventListener("click", function (event) {
  const modal = document.getElementById("playTimeEditorModal");
  if (event.target === modal) {
    closePlayTimeEditor();
  }
});

// Close modals with Escape key
document.addEventListener("keydown", function (event) {
  if (event.key === "Escape") {
    const modal = document.getElementById("playTimeEditorModal");
    if (modal.style.display === "flex") {
      closePlayTimeEditor();
    }
  }
});

// Load on page load if game is selected
document.addEventListener("DOMContentLoaded", function () {
  // Auto-load chart if game is pre-selected
  const gameSelect = document.getElementById("chartGameSelect");
  if (gameSelect && gameSelect.value) {
    // Don't auto-load, wait for user to click Check
  }
});
