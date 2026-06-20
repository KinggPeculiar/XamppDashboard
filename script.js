//   <!-- Vanilla JavaScript -->

  // ==========================================
        // 1. Clock & Date Widget
        // ==========================================
        function updateClock() {
            const now = new Date();
            const timeString = now.toLocaleTimeString(undefined, { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true });
            const dateString = now.toLocaleDateString(undefined, { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });

            document.getElementById('time').textContent = timeString;
            document.getElementById('date').textContent = dateString;
        }
        updateClock();
        setInterval(updateClock, 1000);

        // ==========================================
        // 2. Real-Time Directory Polling & Rendering
        // ==========================================
        let currentProjects = null; // Store current state to prevent useless re-renders
        const projectsGrid = document.getElementById('projectsGrid');
        const searchInput = document.getElementById('searchInput');
        const syncDot = document.getElementById('syncDot');

        // Function to render HTML from JSON data
        function renderProjects(projectsData) {
            projectsGrid.innerHTML = ''; // Clear container

            if (projectsData.length === 0) {
                projectsGrid.innerHTML = '<div class="empty-state">No projects found in htdocs. Create a folder to get started!</div>';
                return;
            }

            const currentFilter = searchInput.value.toLowerCase();

            projectsData.forEach(p => {
                const card = document.createElement('a');
                card.href = p.url;
                card.target = '_blank';
                card.className = 'project-card glass';
                
                // Keep the current search state intact during re-renders
                const titleText = p.name.toLowerCase();
                if (titleText.includes(currentFilter)) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }

                card.innerHTML = `
                    <h3>${p.name}</h3>
                    <p>${p.description}</p>
                `;
                projectsGrid.appendChild(card);
            });
        }

        // Fetch API logic
        async function fetchProjects() {
            try {
                // Visual sync indicator
                syncDot.classList.add('syncing');
                
                // Fetch from our own PHP logic above
                const response = await fetch('?api=projects');
                const newData = await response.json();
                
                // Only update the DOM if the data actually changed (new folder, renamed, deleted)
                if (JSON.stringify(newData) !== JSON.stringify(currentProjects)) {
                    currentProjects = newData;
                    renderProjects(currentProjects);
                }
            } catch (error) {
                console.error("Error fetching projects:", error);
            } finally {
                setTimeout(() => syncDot.classList.remove('syncing'), 300);
            }
        }

        // ==========================================
        // 3. Search Filter Logic (Live DOM manipulation)
        // ==========================================
        searchInput.addEventListener('input', function(e) {
            const filterTerm = e.target.value.toLowerCase();
            const cards = projectsGrid.querySelectorAll('.project-card');

            cards.forEach(card => {
                const title = card.querySelector('h3').textContent.toLowerCase();
                if (title.includes(filterTerm)) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        });

        // Initialize: Fetch immediately on load, then poll every 3 seconds
        fetchProjects();
        setInterval(fetchProjects, 3000);
