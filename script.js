
const STORAGE_KEY = "powerReportFaults";

let reports = [];

try {
  const saved = JSON.parse(localStorage.getItem(STORAGE_KEY) || "[]");
  reports = Array.isArray(saved) ? saved : [];
} catch {
  reports = [];
}

const $ = (selector) => document.querySelector(selector);

function saveReports() {
  try {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(reports));
    return true;
  } catch {
    showToast("Unable to save data in this browser.");
    return false;
  }
}

function escapeHTML(value) {
  return String(value ?? "").replace(/[&<>"']/g, (char) => ({
    "&": "&amp;",
    "<": "&lt;",
    ">": "&gt;",
    '"': "&quot;",
    "'": "&#39;"
  })[char]);
}

function showToast(message) {
  const toast = $("#toast");
  toast.textContent = message;
  toast.classList.add("show");

  clearTimeout(showToast.timer);
  showToast.timer = setTimeout(() => {
    toast.classList.remove("show");
  }, 3000);
}

function showPage(pageName) {
  document.querySelectorAll(".page").forEach(page => {
    page.classList.toggle("hidden", page.id !== pageName);
  });

  document.querySelectorAll(".nav").forEach(button => {
    button.classList.toggle(
      "active",
      button.dataset.page === pageName
    );
  });

  const titles = {
    dashboard: "Dashboard",
    report: "Report Fault",
    reports: "All Reports"
  };

  $("#page-title").textContent = titles[pageName] || "Dashboard";

  if (pageName === "reports") renderReports();
  if (pageName === "dashboard") renderDashboard();

  window.scrollTo({ top: 0, behavior: "smooth" });
}

document.querySelectorAll("[data-page]").forEach(button => {
  button.addEventListener("click", () => {
    showPage(button.dataset.page);
  });
});

document.querySelectorAll("[data-goto]").forEach(button => {
  button.addEventListener("click", () => {
    showPage(button.dataset.goto);
  });
});

function statusClass(status) {
  if (status === "Resolved") return "status-resolved";
  if (status === "In Progress") return "status-in-progress";
  return "status-pending";
}

function statusBadge(status) {
  return `<span class="status ${statusClass(status)}">${escapeHTML(status)}</span>`;
}

function reportTable(items, showActions = false) {
  if (items.length === 0) {
    return `<div class="empty">No fault reports found.</div>`;
  }

  const rows = items.map(report => `
    <tr>
      <td>${escapeHTML(report.id)}</td>
      <td>${escapeHTML(report.name)}</td>
      <td>${escapeHTML(report.type)}</td>
      <td>${escapeHTML(report.location)}</td>
      <td>${escapeHTML(report.date)}</td>
      <td>
        ${showActions ? `
          <select data-status-id="${escapeHTML(report.id)}"
                  aria-label="Update status for ${escapeHTML(report.id)}">
            ${["Pending", "In Progress", "Resolved"].map(status => `
              <option value="${status}"
                ${report.status === status ? "selected" : ""}>
                ${status}
              </option>
            `).join("")}
          </select>
        ` : statusBadge(report.status)}
      </td>
      ${showActions ? `
        <td>
          <button class="action-btn"
            data-delete-id="${escapeHTML(report.id)}">
            Delete
          </button>
        </td>
      ` : ""}
    </tr>
  `).join("");

  return `
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Report ID</th>
            <th>Name</th>
            <th>Fault Type</th>
            <th>Location</th>
            <th>Date</th>
            <th>Status</th>
            ${showActions ? "<th>Action</th>" : ""}
          </tr>
        </thead>
        <tbody>${rows}</tbody>
      </table>
    </div>
  `;
}

function renderDashboard() {
  $("#total-count").textContent = reports.length;
  $("#pending-count").textContent =
    reports.filter(r => r.status === "Pending").length;
  $("#progress-count").textContent =
    reports.filter(r => r.status === "In Progress").length;
  $("#resolved-count").textContent =
    reports.filter(r => r.status === "Resolved").length;

  const recent = [...reports]
    .sort((a, b) => b.createdAt - a.createdAt)
    .slice(0, 5);

  $("#recent-reports").innerHTML = reportTable(recent);
}

function renderReports() {
  const query = $("#search").value.trim().toLowerCase();
  const status = $("#status-filter").value;

  const filtered = [...reports]
    .filter(report => {
      const matchesQuery = [
        report.id,
        report.name,
        report.phone,
        report.type,
        report.location,
        report.description
      ].some(value =>
        String(value).toLowerCase().includes(query)
      );

      return matchesQuery &&
        (!status || report.status === status);
    })
    .sort((a, b) => b.createdAt - a.createdAt);

  $("#all-reports").innerHTML = reportTable(filtered, true);
}

$("#fault-form").addEventListener("submit", event => {
  event.preventDefault();

  const form = event.currentTarget;
  const data = new FormData(form);

  const report = {
    id: "FR-" + Date.now().toString(36).toUpperCase(),
    name: data.get("name").trim(),
    phone: data.get("phone").trim(),
    type: data.get("type"),
    location: data.get("location").trim(),
    description: data.get("description").trim(),
    date: data.get("date"),
    status: "Pending",
    createdAt: Date.now()
  };

  if (!report.name || !report.phone ||
      !report.type || !report.location ||
      !report.description || !report.date) {
    showToast("Please complete all required fields.");
    return;
  }

  reports.push(report);

  if (!saveReports()) {
    reports.pop();
    return;
  }

  form.reset();
  showToast("Fault report submitted successfully!");
  renderDashboard();
  showPage("reports");
});

$("#search").addEventListener("input", renderReports);
$("#status-filter").addEventListener("change", renderReports);

$("#all-reports").addEventListener("change", event => {
  const select = event.target.closest("[data-status-id]");
  if (!select) return;

  const report = reports.find(r => r.id === select.dataset.statusId);
  if (!report) return;

  const oldStatus = report.status;
  report.status = select.value;

  if (!saveReports()) {
    report.status = oldStatus;
    renderReports();
    return;
  }

  renderReports();
  renderDashboard();
  showToast("Report status updated.");
});

$("#all-reports").addEventListener("click", event => {
  const button = event.target.closest("[data-delete-id]");
  if (!button) return;

  const id = button.dataset.deleteId;
  const report = reports.find(r => r.id === id);
  if (!report) return;

  if (!confirm(`Delete report ${id}? This cannot be undone.`)) {
    return;
  }

  const previous = reports;
  reports = reports.filter(r => r.id !== id);

  if (!saveReports()) {
    reports = previous;
    return;
  }

  renderReports();
  renderDashboard();
  showToast("Report deleted.");
});

// Set today's date automatically.
const today = new Date();
const localToday = [
  today.getFullYear(),
  String(today.getMonth() + 1).padStart(2, "0"),
  String(today.getDate()).padStart(2, "0")
].join("-");

$('input[name="date"]').value = localToday;

renderDashboard();
renderReports();
