// External Dependencies
import React, {Component} from 'react';

// Internal Dependencies
import './style.css';

class WpDataTable extends Component {

  static slug = 'DIVI_wpDataTable';

  mounted = false;
  shortcode = '[wpdatatable]';

  constructor(props) {
    super(props);
    this.state = {
      shortcode: this.shortcode
    };
  }

  createShortCode () {
    let shortcode = '[wpdatatable'
    let tableId = this.props.id;
    let view = this.props.view;
    let var1 = this.props.var1;
    let var2 = this.props.var2;
    let var3 = this.props.var3;
    let var4 = this.props.var4;
    let var5 = this.props.var5;
    let var6 = this.props.var6;
    let var7 = this.props.var7;
    let var8 = this.props.var8;
    let var9 = this.props.var9;
    let export_file_name = this.props.export_file_name;
    let tablesCount = parseInt(this.props.table_array_length);

    if (tablesCount === 1) {
      return "Please create a wpDataTable first. You can find detailed instructions in our docs on this <a target='_blank' href='https://wpdatatables.com/documentation/general/features-overview/'>link</a>.";
    }

    if (!isNumeric(tableId)) {
      tableId = tableId.substring(tableId.lastIndexOf('(id: ') + 4);
      tableId = tableId.substring(0, tableId.lastIndexOf(')'));
    }

    if (!parseInt(tableId)) {
      return 'Please select a wpDataTable.';
    }
    shortcode += ' id=' + tableId;
    if (view) {
      shortcode += view === 'excel-like' ? ' table_view=excel' : ' table_view=regular';
    }
    if (var1) {
      shortcode += ' var1=' + var1;
    }
    if (var2) {
      shortcode += ' var2=' + var2;
    }
    if (var3) {
      shortcode += ' var3=' + var3;
    }
    if (var4) {
      shortcode += ' var4=' + var4;
    }
    if (var5) {
      shortcode += ' var5=' + var5;
    }
    if (var6) {
      shortcode += ' var6=' + var6;
    }
    if (var7) {
      shortcode += ' var7=' + var7;
    }
    if (var8) {
      shortcode += ' var8=' + var8;
    }
    if (var9) {
      shortcode += ' var9=' + var9;
    }
    if (export_file_name) {
      shortcode += ' export_file_name=' + export_file_name;
    }
    shortcode += ']'
    return shortcode

  }

  componentDidMount () {
    this.mounted = true
    if (this.mounted) {
      this.shortcode = this.createShortCode()
      this.setState({
        shortcode: this.shortcode
      })
    }
  }

  componentDidUpdate (prevProps, prevState, snapshot) {
    if (this.mounted) {
      this.shortcode = this.createShortCode()
      if (prevState.shortcode !== this.shortcode) {
        this.setState({
          shortcode: this.shortcode
        })
      }
    }
  }

  componentWillUnmount () {
    this.mounted = false
  }

  render () {
    return (
        <div dangerouslySetInnerHTML={{ __html: this.shortcode }}/>
    )
  }

}
function isNumeric(str) {
  if (typeof str != "string") return false
  return !isNaN(str) && !isNaN(parseFloat(str))
}

export default WpDataTable;
