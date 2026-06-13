const orderTeamMembers = () => {
    const gridShow = document.querySelector('.js-bpp-team-grid-show');

    if (!gridShow) {
        return;
    }
    const teamMembers = Array.from(gridShow.children);

    teamMembers.sort((a, b) => {
        const countA = parseInt(a.dataset.count, 10);
        const countB = parseInt(b.dataset.count, 10);
        return countA - countB;
    });

    teamMembers.forEach(member => gridShow.appendChild(member));
};

const filterTeamMembers = searchQuery => {
    const gridShow = document.querySelector('.js-bpp-team-grid-show');
    const gridHide = document.querySelector('.js-bpp-team-grid-hide');
    const teamMembers = document.querySelectorAll('.js-bpp-team-grid-item');

    if (!gridShow || !gridHide || !teamMembers) {
        return;
    }
    for (const member of teamMembers) {
        const name = member.querySelector('.js-bpp-team-grid-name');
        const nameContent = name.textContent.toLowerCase();
        const matches = nameContent.includes(searchQuery.toLowerCase());

        if (matches) {
            gridShow.appendChild(member);
        } else {
            gridHide.appendChild(member);
        }
    }
};

const initTeamGrid = () => {
    const inputSearch = document.querySelector('.js-bpp-team-grid-search');

    if (!inputSearch) {
        return;
    }
    inputSearch.addEventListener('input', event => {
        const searchQuery = event.target.value;

        filterTeamMembers(searchQuery);
        orderTeamMembers();
    });
};

document.addEventListener('DOMContentLoaded', function () {
    initTeamGrid();
});
