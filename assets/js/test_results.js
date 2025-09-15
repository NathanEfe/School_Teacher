function showSection(sectionId) {
  // Hide all sections
  const sections = document.querySelectorAll('.form-section');
  sections.forEach(sec => sec.style.display = 'none');

  // Show the selected one
  document.getElementById(sectionId).style.display = 'block';
}