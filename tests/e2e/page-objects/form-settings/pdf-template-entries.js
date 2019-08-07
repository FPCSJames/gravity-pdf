import { Selector, t } from 'testcafe'
import { admin, baseURL } from '../../auth'
import Form from './form'

const form = new Form()

class PdfTemplateEntries {
  constructor () {
    this.name = Selector('#gfpdf_settings\\[name\\]')
    this.fileName = Selector('#gfpdf_settings\\[filename\\]')
    this.template = Selector('.alternate')
    this.backToTemplateListLink = Selector('a').withText('Back to PDF list.')
    this.templateList = Selector('#the-list')
    this.templateDetail = Selector('.alternate').find('td').nth(0)
    this.addPdfButton = Selector('div').find('[class^="button-primary"][value="Add PDF"]')
    this.toggleSwitch = Selector('.check-column').find('img')
    this.inActiveTemplate = Selector('div').find('[alt^="Inactive"][title="Inactive"]')
    this.activeTemplate = Selector('div').find('[alt^="Active"][title="Active"]')
    this.entryItem = Selector('td').withAttribute('data-colname', 'Text')
    this.viewPdfLink = Selector('a').withText('View PDF')
    this.editLink = Selector('span').withText('Edit')
    this.updatePdfButton = Selector('div').find('[class^="button-primary"][value="Update PDF"]')
    this.options = Selector('div').find('[class^="name column-name has-row-actions column-primary"]')
    this.duplicateLink = Selector('a').withText('Duplicate')
    this.deletePDF = Selector('.submitdelete')
    this.confirmDelete = Selector('button').find('[class^="ui-button-text"]').withText('Delete')
  }

  async navigatePdfEntries (text) {
    await t
      .useRole(admin)
      .navigateTo(`${baseURL}/wp-admin/admin.php?page=${text}`)
      .hover(form.list)
      .click(form.entries)
  }
}

export default PdfTemplateEntries
