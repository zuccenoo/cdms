const calendarDays = document.getElementById("calendar-days");
const monthName = document.getElementById("monthName");
const yearNumber = document.getElementById("yearNumber");
const filterLabel = document.getElementById("filter-label");

const months = [
  "January", "February", "March", "April", "May", "June",
  "July", "August", "September", "October", "November", "December"
];

let currentDate = new Date();

function renderCalendar(date) {
  const year = date.getFullYear();
  const month = date.getMonth();

  monthName.textContent = months[month];
  yearNumber.textContent = year;

  const firstDay = new Date(year, month, 1).getDay();
  const daysInMonth = new Date(year, month + 1, 0).getDate();

  calendarDays.innerHTML = "";

  const daysOfWeek = ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"];
  daysOfWeek.forEach(day => {
    const dayHeader = document.createElement("div");
    dayHeader.classList.add("day-header");
    dayHeader.textContent = day;
    calendarDays.appendChild(dayHeader);
  });

  let startDay = firstDay === 0 ? 6 : firstDay - 1;

  for (let i = 0; i < startDay; i++) {
    const emptyCell = document.createElement("div");
    emptyCell.classList.add("calendar-day", "empty");
    calendarDays.appendChild(emptyCell);
  }

  for (let day = 1; day <= daysInMonth; day++) {
    const dayCell = document.createElement("div");
    dayCell.classList.add("calendar-day");
    dayCell.textContent = day;
    calendarDays.appendChild(dayCell);
  }
}

renderCalendar(currentDate);

// Month navigation
document.getElementById("prevMonth").addEventListener("click", () => {
  currentDate.setMonth(currentDate.getMonth() - 1);
  renderCalendar(currentDate);
  filterLabel.textContent = ""; // Clear label on month change
});

document.getElementById("nextMonth").addEventListener("click", () => {
  currentDate.setMonth(currentDate.getMonth() + 1);
  renderCalendar(currentDate);
  filterLabel.textContent = ""; // Clear label on month change
});

// Filter buttons with label effect only
document.querySelectorAll(".filter-btn").forEach(button => {
  button.addEventListener("click", () => {
    // Highlight active button
    document.querySelectorAll(".filter-btn").forEach(btn => btn.classList.remove("active"));
    button.classList.add("active");

    // Update label
    const type = button.dataset.type;
    let labelText = "";

    if (type === "confirmed") labelText = "Confirmed Reservations";
    else if (type === "pending") labelText = "Pending Reservations";
    else if (type === "pencil") labelText = "Pencil Bookings";

    filterLabel.textContent = labelText;

    // Render calendar without colors
    renderCalendar(currentDate);
  });
});
