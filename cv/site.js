document.getElementById('print-cv')?.addEventListener('click', () => window.print());

// The CV is served by both the historical Apache layout and the unified router.
const cvLegacyMount = location.pathname.match(/^(.*?)\/Jovani\/cv(?:\/|\.html|$)/i);
const cvStaticMount = location.pathname.match(/^(.*?)\/cv\/cv\.html$/i);
const cvLegacyPrefix = cvLegacyMount?.[1] ?? cvStaticMount?.[1];
const cvPortfolioBase = cvLegacyPrefix !== undefined ? `${cvLegacyPrefix}/Jovani/` : '/portfolio/';
document.querySelectorAll('[data-cv-case]').forEach(link => {
  link.href = `${cvPortfolioBase}casos/${link.dataset.cvCase}`;
});
document.querySelectorAll('[data-cv-portfolio]').forEach(link => { link.href = cvPortfolioBase; });
const cvBackLink = document.querySelector('.cv-tools a');
if (cvBackLink) cvBackLink.href = cvPortfolioBase;
const cvBlogLink = document.querySelector('.cv-tools a:nth-child(2)');
if (cvBlogLink) cvBlogLink.href = cvLegacyPrefix !== undefined ? `${cvLegacyPrefix}/blog/` : '/';
